<?php

namespace App\Containers\AppSection\Page\Tasks;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindPageTask extends ParentTask
{
    /**
     * @param array<int, string> $with
     */
    public function run(int $id, array $with = []): Page
    {
        $with = LanguageAdvancedManager::withTranslations($with, Page::class);

        return Page::query()->with($with)->findOrFail($id);
    }
}
