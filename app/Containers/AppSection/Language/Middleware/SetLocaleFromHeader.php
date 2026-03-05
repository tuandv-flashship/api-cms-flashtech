<?php

namespace App\Containers\AppSection\Language\Middleware;

use App\Containers\AppSection\Language\Supports\LanguageLocaleCache;
use App\Ship\Parents\Middleware\Middleware as ParentMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

final class SetLocaleFromHeader extends ParentMiddleware
{
    /**
     * @param \Closure(Request): mixed $next
     */
    public function handle(Request $request, \Closure $next): mixed
    {
        $locale = $request->header('X-Locale');

        if ($locale) {
            $resolvedLocale = LanguageLocaleCache::resolveToLangLocale($locale);

            if ($resolvedLocale !== null) {
                App::setLocale($resolvedLocale);
            }
        }

        return $next($request);
    }

    /**
     * Call this when languages are created/updated/deleted to refresh the cache.
     * Kept for backward compatibility — delegates to LanguageLocaleCache.
     */
    public static function clearCache(): void
    {
        LanguageLocaleCache::clearCache();
    }
}

