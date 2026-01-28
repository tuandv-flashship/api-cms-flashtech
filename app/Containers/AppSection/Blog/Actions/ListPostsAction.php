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
     * @param array<string, mixed> $filters
     */
    public function run(array $filters, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->listPostsTask->run($filters, $perPage, $page);
    }
}
