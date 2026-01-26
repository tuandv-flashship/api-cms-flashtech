<?php

namespace App\Containers\AppSection\LanguageAdvanced\Tasks;

use App\Containers\AppSection\LanguageAdvanced\Models\SlugTranslation;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpsertSlugTranslationTask extends ParentTask
{
    public function run(int $slugId, string $langCode, string $key, ?string $prefix = null): SlugTranslation
    {
        return SlugTranslation::query()->updateOrCreate(
            [
                'lang_code' => $langCode,
                'slugs_id' => $slugId,
            ],
            [
                'key' => $key,
                'prefix' => $prefix ?? '',
            ],
        );
    }
}
