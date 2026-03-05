<?php

namespace App\Containers\AppSection\Translation\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

final class Translation extends Model
{
    protected $table = 'translations';

    protected $fillable = [
        'locale',
        'group_key',
        'item_key',
        'value',
    ];

    // ─── Cache helpers ───────────────────────────────────────────────

    /**
     * Get all translations for a group+locale (used by TranslationLoaderManager).
     *
     * @return array<string, string>
     */
    public static function getForGroup(string $locale, string $group): array
    {
        return Cache::rememberForever(
            static::cacheKey($locale, $group),
            static function () use ($locale, $group): array {
                $rows = static::query()
                    ->where('locale', $locale)
                    ->where('group_key', $group)
                    ->pluck('value', 'item_key')
                    ->toArray();

                // Rebuild nested array for dotted keys (e.g. "custom.email.required")
                if ($group !== '*') {
                    $nested = [];
                    foreach ($rows as $key => $value) {
                        Arr::set($nested, $key, $value);
                    }

                    return $nested;
                }

                return $rows;
            },
        );
    }

    /**
     * Get ALL translations for a locale (used by public FE API).
     *
     * @return array<string, array<string, string>>
     */
    public static function getAllForLocale(string $locale): array
    {
        return Cache::rememberForever(
            static::cacheKey($locale, '_all'),
            static fn (): array => static::query()
                ->where('locale', $locale)
                ->get()
                ->groupBy('group_key')
                ->map(fn ($items) => $items->pluck('value', 'item_key')->toArray())
                ->toArray(),
        );
    }

    // ─── Cache invalidation ──────────────────────────────────────────

    protected static function booted(): void
    {
        $flush = static fn (self $translation): null => $translation->flushGroupCache();

        static::saved($flush);
        static::deleted($flush);
    }

    public function flushGroupCache(): void
    {
        Cache::forget(static::cacheKey($this->locale, $this->group_key));
        Cache::forget(static::cacheKey($this->locale, '_all'));
    }

    /**
     * Flush all translation caches for a locale.
     */
    public static function flushLocaleCache(string $locale): void
    {
        $groups = static::query()
            ->where('locale', $locale)
            ->distinct()
            ->pluck('group_key')
            ->toArray();

        foreach ($groups as $group) {
            Cache::forget(static::cacheKey($locale, $group));
        }

        Cache::forget(static::cacheKey($locale, '_all'));
    }

    // ─── Scopes ──────────────────────────────────────────────────────

    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    public function scopeForGroup(Builder $query, string $group): Builder
    {
        return $query->where('group_key', $group);
    }

    // ─── Helpers ─────────────────────────────────────────────────────

    private static function cacheKey(string $locale, string $group): string
    {
        return "translations.{$locale}.{$group}";
    }
}
