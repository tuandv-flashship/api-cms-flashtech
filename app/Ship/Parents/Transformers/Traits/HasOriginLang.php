<?php

namespace App\Ship\Parents\Transformers\Traits;

use App\Ship\Supports\Language;

trait HasOriginLang
{
    /**
     * Get the origin language code for translatable content.
     * This indicates the language of the original/source content before translations.
     */
    protected function getOriginLang(): string
    {
        $defaultLanguage = Language::getDefaultLanguage();

        return $defaultLanguage['lang_code']
            ?? $defaultLanguage['code']
            ?? config('app.locale', 'en');
    }
}
