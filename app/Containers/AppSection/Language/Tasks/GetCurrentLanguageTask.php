<?php

namespace App\Containers\AppSection\Language\Tasks;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class GetCurrentLanguageTask extends ParentTask
{
    public function run(string|null $locale): Language
    {
        $language = null;

        if ($locale) {
            $language = Language::query()
                ->where('lang_locale', $locale)
                ->orWhere('lang_code', $locale)
                ->first();
        }

        if (! $language) {
            $language = Language::query()->where('lang_is_default', 1)->first();
        }

        return $language ?? Language::query()->orderBy('lang_order')->firstOrFail();
    }
}
