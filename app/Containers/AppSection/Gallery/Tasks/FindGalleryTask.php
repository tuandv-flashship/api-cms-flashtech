<?php

namespace App\Containers\AppSection\Gallery\Tasks;

use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindGalleryTask extends ParentTask
{
    /**
     * @param array<int, string> $with
     */
    public function run(int $id, array $with = []): Gallery
    {
        $with = LanguageAdvancedManager::withTranslations($with, Gallery::class);

        $langCode = LanguageAdvancedManager::getTranslationLocale();
        if ($langCode && ! LanguageAdvancedManager::isDefaultLocale($langCode)) {
            $with['meta.translations'] = static fn ($query) => $query->where('lang_code', $langCode);
        }

        return Gallery::query()->with($with)->findOrFail($id);
    }
}
