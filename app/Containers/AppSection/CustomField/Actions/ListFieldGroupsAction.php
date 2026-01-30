<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Tasks\ListFieldGroupsTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListFieldGroupsAction extends ParentAction
{
    public function __construct(
        private readonly ListFieldGroupsTask $listFieldGroupsTask,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->listFieldGroupsTask->run($filters, $perPage, $page);
    }
}
