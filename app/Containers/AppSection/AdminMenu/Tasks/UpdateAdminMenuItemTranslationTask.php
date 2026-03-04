<?php

namespace App\Containers\AppSection\AdminMenu\Tasks;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateAdminMenuItemTranslationTask extends ParentTask
{
    /**
     * @param array<string, mixed> $data
     */
    public function run(int $id, string $langCode, array $data): AdminMenuItem
    {
        $item = AdminMenuItem::query()->findOrFail($id);

        LanguageAdvancedManager::saveTranslation($item, $data, $langCode);

        // Reload with translations for the response.
        $with = LanguageAdvancedManager::withTranslations([], AdminMenuItem::class, $langCode);

        return AdminMenuItem::query()->with($with)->findOrFail($id);
    }
}
