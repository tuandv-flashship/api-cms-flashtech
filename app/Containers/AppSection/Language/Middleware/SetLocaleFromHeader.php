<?php

namespace App\Containers\AppSection\Language\Middleware;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Middleware\Middleware as ParentMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

final class SetLocaleFromHeader extends ParentMiddleware
{
    private const CACHE_KEY = 'languages:locale_map';

    /**
     * @param \Closure(Request): mixed $next
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $locale = $request->header('X-Locale');

        if ($locale) {
            $resolvedLocale = $this->resolveLocale($locale);

            if ($resolvedLocale !== null) {
                App::setLocale($resolvedLocale);
            }
        }

        return $next($request);
    }

    private function resolveLocale(string $locale): ?string
    {
        $map = $this->getLocaleMap();

        return $map[$locale] ?? null;
    }

    /**
     * Build a lookup map: [input_value => lang_locale]
     * Cached forever, invalidated when languages change.
     *
     * @return array<string, string>
     */
    private function getLocaleMap(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, static function (): array {
            $map = [];

            $languages = Language::query()->get(['lang_locale', 'lang_code']);

            foreach ($languages as $language) {
                // Map both lang_locale and lang_code to the canonical lang_locale
                $map[$language->lang_locale] = $language->lang_locale;

                if ($language->lang_code !== $language->lang_locale) {
                    $map[$language->lang_code] = $language->lang_locale;
                }
            }

            return $map;
        });
    }

    /**
     * Call this when languages are created/updated/deleted to refresh the cache.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
