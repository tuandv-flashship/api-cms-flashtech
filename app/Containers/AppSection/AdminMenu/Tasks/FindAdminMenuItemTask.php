<?php

namespace App\Containers\AppSection\AdminMenu\Tasks;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindAdminMenuItemTask extends ParentTask
{
    /**
     * @param array<int, string> $with
     */
    public function run(int $id, array $with = []): AdminMenuItem
    {
        $with = LanguageAdvancedManager::withTranslations($with, AdminMenuItem::class);

        return AdminMenuItem::query()->with($with)->findOrFail($id);
    }
}
