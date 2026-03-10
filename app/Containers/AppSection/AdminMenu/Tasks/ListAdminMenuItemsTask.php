<?php

namespace App\Containers\AppSection\AdminMenu\Tasks;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

final class ListAdminMenuItemsTask extends ParentTask
{
    /**
     * Flat query: loads ALL active items + translations in 2 queries.
     */
    public function run(bool $activeOnly = true): Collection
    {
        $query = AdminMenuItem::query();

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        $with = LanguageAdvancedManager::withTranslations(['allTranslations'], AdminMenuItem::class);

        return $query->with($with)->orderBy('priority')->get();
    }
}
