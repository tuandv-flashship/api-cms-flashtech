<?php

namespace App\Containers\AppSection\Gallery\Actions;

use App\Containers\AppSection\Gallery\Tasks\ListGalleriesTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListGalleriesAction extends ParentAction
{
    public function __construct(
        private readonly ListGalleriesTask $listGalleriesTask,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->listGalleriesTask->run($filters, $perPage, $page);
    }
}
