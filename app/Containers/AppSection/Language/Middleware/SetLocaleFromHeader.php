<?php

namespace App\Containers\AppSection\Language\Middleware;

use App\Containers\AppSection\Language\Models\Language;
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
            $language = Language::query()
                ->where('lang_locale', $locale)
                ->orWhere('lang_code', $locale)
                ->first();

            if ($language) {
                App::setLocale($language->lang_locale);
            }
        }

        return $next($request);
    }
}
