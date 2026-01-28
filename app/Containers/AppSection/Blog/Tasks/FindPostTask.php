<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindPostTask extends ParentTask
{
    /**
     * @param array<int, string> $with
     */
    public function run(int $id, array $with = []): Post
    {
        $with = LanguageAdvancedManager::withTranslations($with, Post::class);

        return Post::query()->with($with)->findOrFail($id);
    }
}
