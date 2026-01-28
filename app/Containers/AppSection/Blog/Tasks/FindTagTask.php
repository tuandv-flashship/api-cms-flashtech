<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindTagTask extends ParentTask
{
    /**
     * @param array<int, string> $with
     */
    public function run(int $id, array $with = []): Tag
    {
        $with = LanguageAdvancedManager::withTranslations($with, Tag::class);

        return Tag::query()->with($with)->findOrFail($id);
    }
}
