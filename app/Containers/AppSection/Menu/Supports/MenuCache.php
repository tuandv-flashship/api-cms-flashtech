<?php

namespace App\Containers\AppSection\Menu\Supports;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Menu\Models\MenuLocation;
use Closure;
use Illuminate\Support\Facades\Cache;

final class MenuCache
{
    /**
     * @template T
     *
     * @param Closure(): T $callback
     * @return T
     */
    public function rememberByLocation(string $location, ?string $locale, Closure $callback): mixed
    {
        $resolvedLocale = $this->resolveLocale($locale);
        $ttlSeconds = (int) config('menu.cache.ttl_seconds', 86400);

        return Cache::remember(
            $this->buildKey($location, $resolvedLocale),
            $ttlSeconds,
            $callback,
        );
    }

    public function forgetByMenuId(int $menuId): void
    {
        $locations = MenuLocation::query()
            ->where('menu_id', $menuId)
            ->distinct()
            ->pluck('location')
            ->filter(fn (mixed $value) => is_string($value) && $value !== '')
            ->values()
            ->all();

        foreach ($locations as $location) {
            $this->forgetByLocation($location);
        }
    }

    public function forgetByLocation(string $location): void
    {
        $versionKey = $this->buildVersionKey($location);

        if (! Cache::add($versionKey, 2)) {
            Cache::increment($versionKey);
        }
    }

    private function buildKey(string $location, string $locale): string
    {
        $version = $this->getLocationVersion($location);

        return sprintf('menu:location:%s:version:%d:locale:%s', $location, $version, $locale);
    }

    private function buildVersionKey(string $location): string
    {
        return sprintf('menu:location:%s:version', $location);
    }

    private function getLocationVersion(string $location): int
    {
        $versionKey = $this->buildVersionKey($location);
        $version = (int) Cache::rememberForever($versionKey, static fn (): int => 1);

        if ($version > 0) {
            return $version;
        }

        Cache::forever($versionKey, 1);

        return 1;
    }

    private function resolveLocale(?string $locale): string
    {
        if ($locale !== null && $locale !== '') {
            $normalized = LanguageAdvancedManager::normalizeLanguageCode($locale);
            if ($normalized !== null) {
                return $normalized;
            }

            return $locale;
        }

        $fromRequest = request()->header('X-Locale');
        if (is_string($fromRequest) && $fromRequest !== '') {
            $normalized = LanguageAdvancedManager::normalizeLanguageCode($fromRequest);

            return $normalized ?? $fromRequest;
        }

        return (string) app()->getLocale();
    }
}
