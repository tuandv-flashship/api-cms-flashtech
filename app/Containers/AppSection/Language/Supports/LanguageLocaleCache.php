<?php

namespace App\Containers\AppSection\Language\Supports;

use App\Containers\AppSection\Language\Models\Language;
use Illuminate\Support\Facades\Cache;

/**
 * Single source of truth for locale lookups.
 * All locale map and default locale queries are cached via Cache::rememberForever
 * and invalidated when languages are created/updated/deleted.
 */
final class LanguageLocaleCache
{
    private const LOCALE_MAP_KEY = 'languages:locale_map';
    private const DEFAULT_LOCALE_KEY = 'languages:default_locale_code';

    /**
     * Get the locale map: maps both lang_code and lang_locale to the canonical lang_code.
     *
     * @return array<string, string>
     */
    public static function getLocaleMap(): array
    {
        return Cache::rememberForever(self::LOCALE_MAP_KEY, static function (): array {
            $map = [];

            $languages = Language::query()->get(['lang_locale', 'lang_code']);

            foreach ($languages as $language) {
                $map[$language->lang_code] = $language->lang_code;

                if ($language->lang_locale !== $language->lang_code) {
                    $map[$language->lang_locale] = $language->lang_code;
                }
            }

            return $map;
        });
    }

    /**
     * Normalize a locale string (lang_code or lang_locale) to the canonical lang_code.
     */
    public static function normalizeLanguageCode(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return self::getLocaleMap()[$value] ?? null;
    }

    /**
     * Resolve a locale string to the canonical lang_locale (for App::setLocale).
     */
    public static function resolveToLangLocale(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return self::getLangLocaleMap()[$value] ?? null;
    }

    /**
     * Get the default language code.
     */
    public static function getDefaultLocaleCode(): ?string
    {
        return Cache::rememberForever(self::DEFAULT_LOCALE_KEY, static function (): ?string {
            return Language::query()
                ->where('lang_is_default', true)
                ->value('lang_code');
        });
    }

    /**
     * Clear all cached locale data.
     * Must be called when languages are created, updated, or deleted.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::LOCALE_MAP_KEY);
        Cache::forget(self::DEFAULT_LOCALE_KEY);
    }

    /**
     * Build a map: [input_value => lang_locale] for App::setLocale().
     *
     * @return array<string, string>
     */
    private static function getLangLocaleMap(): array
    {
        // Reuse the same cache query, just map to lang_locale instead
        static $langLocaleMap = null;

        if ($langLocaleMap !== null) {
            return $langLocaleMap;
        }

        $langLocaleMap = Cache::rememberForever(self::LOCALE_MAP_KEY . ':locale', static function (): array {
            $map = [];

            $languages = Language::query()->get(['lang_locale', 'lang_code']);

            foreach ($languages as $language) {
                $map[$language->lang_locale] = $language->lang_locale;

                if ($language->lang_code !== $language->lang_locale) {
                    $map[$language->lang_code] = $language->lang_locale;
                }
            }

            return $map;
        });

        return $langLocaleMap;
    }
}
