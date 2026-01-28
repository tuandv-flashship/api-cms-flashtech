<?php

namespace App\Containers\AppSection\LanguageAdvanced\Supports;

use App\Containers\AppSection\Language\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

final class LanguageAdvancedManager
{
    private const LOCALE_CACHE_KEY = '_language_advanced_locale';
    private const DEFAULT_LOCALE_CACHE_KEY = '_language_advanced_default_locale';
    private const DEFAULT_LOCALE_CODE_CACHE_KEY = '_language_advanced_default_locale_code';

    public static function clearLocaleCache(): void
    {
        $request = request();

        $request->attributes->remove(self::LOCALE_CACHE_KEY);
        $request->attributes->remove(self::DEFAULT_LOCALE_CACHE_KEY);
        $request->attributes->remove(self::DEFAULT_LOCALE_CODE_CACHE_KEY);
    }

    public static function setTranslationLocale(string $langCode): void
    {
        $request = request();
        $request->attributes->set(self::LOCALE_CACHE_KEY, $langCode);
        $request->attributes->set(self::DEFAULT_LOCALE_CACHE_KEY, $langCode === self::getDefaultLocaleCode());
    }

    public static function getTranslationLocale(): ?string
    {
        $request = request();

        if ($request->attributes->has(self::LOCALE_CACHE_KEY)) {
            return $request->attributes->get(self::LOCALE_CACHE_KEY);
        }

        $locale = self::resolveLocaleFromRequest($request);
        $langCode = self::normalizeLanguageCode($locale);

        if (! $langCode) {
            $langCode = self::getDefaultLocaleCode();
        }

        if ($langCode) {
            $request->attributes->set(self::LOCALE_CACHE_KEY, $langCode);
        }

        return $langCode;
    }

    public static function isDefaultLocale(?string $langCode = null): bool
    {
        $request = request();

        if ($langCode === null && $request->attributes->has(self::DEFAULT_LOCALE_CACHE_KEY)) {
            return (bool) $request->attributes->get(self::DEFAULT_LOCALE_CACHE_KEY);
        }

        $langCode = $langCode ?? self::getTranslationLocale();
        $isDefault = $langCode !== null && $langCode === self::getDefaultLocaleCode();

        $request->attributes->set(self::DEFAULT_LOCALE_CACHE_KEY, $isDefault);

        return $isDefault;
    }

    public static function normalizeLanguageCode(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $language = Language::query()
            ->where('lang_code', $value)
            ->orWhere('lang_locale', $value)
            ->first();

        return $language?->lang_code;
    }

    public static function getDefaultLocaleCode(): ?string
    {
        $request = request();

        if ($request->attributes->has(self::DEFAULT_LOCALE_CODE_CACHE_KEY)) {
            return $request->attributes->get(self::DEFAULT_LOCALE_CODE_CACHE_KEY);
        }

        $code = Language::query()
            ->where('lang_is_default', true)
            ->value('lang_code');

        $request->attributes->set(self::DEFAULT_LOCALE_CODE_CACHE_KEY, $code);

        return $code;
    }

    public static function isSupported(Model|string|null $model): bool
    {
        if (! $model) {
            return false;
        }

        if (is_object($model)) {
            $model = $model::class;
        }

        return in_array($model, self::supportedModels(), true);
    }

    /**
     * @return array<int, string>
     */
    public static function supportedModels(): array
    {
        return array_keys(self::getSupported());
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function getSupported(): array
    {
        return config('language-advanced.supported', []);
    }

    /**
     * @return array<int, string>
     */
    public static function getTranslatableColumns(Model|string|null $model): array
    {
        if (! $model) {
            return [];
        }

        if (is_object($model)) {
            $model = $model::class;
        }

        return Arr::get(self::getSupported(), $model, []);
    }

    /**
     * @param array<int|string, mixed> $with
     * @return array<int|string, mixed>
     */
    public static function withTranslations(array $with, Model|string $model, ?string $langCode = null): array
    {
        if (! self::isSupported($model)) {
            return $with;
        }

        $langCode = $langCode ?? self::getTranslationLocale();
        if (! $langCode || self::isDefaultLocale($langCode)) {
            return $with;
        }

        $table = self::getTranslationTable($model);

        $with['translations'] = static function ($query) use ($table, $langCode): void {
            $query->where($table . '.lang_code', $langCode);
        };

        return $with;
    }

    public static function translateAttribute(Model $model, string $key, mixed $value): mixed
    {
        if (! self::isSupported($model) || self::isDefaultLocale()) {
            return $value;
        }

        if (! $model->relationLoaded('translations')) {
            return $value;
        }

        $langCode = self::getTranslationLocale();
        if (! $langCode) {
            return $value;
        }

        $translation = $model->translations->firstWhere('lang_code', $langCode);
        if (! $translation) {
            return $value;
        }

        $translated = $translation->{$key} ?? null;

        return $translated !== null ? $translated : $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function saveTranslation(Model $model, array $data, string $langCode): bool
    {
        if (! self::isSupported($model)) {
            return false;
        }

        $columns = self::getTranslatableColumns($model);
        if ($columns === []) {
            return false;
        }

        $table = self::getTranslationTable($model);
        $foreignKey = self::getTranslationForeignKey($model);

        $payload = Arr::only($data, $columns);
        $payload = array_merge($payload, [
            'lang_code' => $langCode,
            $foreignKey => $model->getKey(),
        ]);

        $condition = [
            'lang_code' => $langCode,
            $foreignKey => $model->getKey(),
        ];

        return (bool) \DB::table($table)->updateOrInsert($condition, $payload);
    }

    public static function applyTranslationsToQuery(Builder $query, Model|string $model, ?string $langCode = null): Builder
    {
        $with = self::withTranslations([], $model, $langCode);
        if ($with === []) {
            return $query;
        }

        return $query->with($with);
    }

    public static function getTranslationTable(Model|string $model): string
    {
        if (is_string($model)) {
            $model = new $model();
        }

        return $model->getTable() . '_translations';
    }

    public static function getTranslationForeignKey(Model|string $model): string
    {
        if (is_string($model)) {
            $model = new $model();
        }

        return $model->getTable() . '_id';
    }

    private static function resolveLocaleFromRequest(Request $request): ?string
    {
        return $request->header('X-Locale') ?: app()->getLocale();
    }
}
