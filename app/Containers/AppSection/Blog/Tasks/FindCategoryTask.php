<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindCategoryTask extends ParentTask
{
    /**
     * @param array<int, string> $with
     */
    public function run(int $id, array $with = []): Category
    {
        $with = LanguageAdvancedManager::withTranslations($with, Category::class);

        return Category::query()->with($with)->findOrFail($id);
    }
}
