<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Data\Repositories\TagRepository;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTagsTask extends ParentTask
{
    public function __construct(
        private readonly TagRepository $repository,
    ) {
    }

    public function run(): LengthAwarePaginator
    {
        $with = LanguageAdvancedManager::withTranslations(
            ['slugable'],
            Tag::class
        );

        return $this->repository
            ->scope(static fn ($query) => $query->with($with))
            ->addRequestCriteria()
            ->paginate();
    }
}
