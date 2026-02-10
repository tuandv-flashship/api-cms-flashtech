<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Tasks\ListPostsTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPostsAction extends ParentAction
{
    public function __construct(
        private readonly ListPostsTask $listPostsTask,
    ) {
    }

    /**
     * @param array<string, mixed> $relationFilters Relationship-based filters (category_ids, tag_ids)
     */
    public function run(array $relationFilters = []): LengthAwarePaginator
    {
        return $this->listPostsTask->run($relationFilters);
    }
}
