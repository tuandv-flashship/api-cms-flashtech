<?php

namespace App\Containers\AppSection\CustomField\Tasks;

use App\Containers\AppSection\CustomField\Data\Repositories\FieldGroupRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListFieldGroupsTask extends ParentTask
{
    public function __construct(
        private readonly FieldGroupRepository $repository,
    ) {
    }

    public function run(): LengthAwarePaginator
    {
        return $this->repository
            ->addRequestCriteria()
            ->paginate();
    }
}
