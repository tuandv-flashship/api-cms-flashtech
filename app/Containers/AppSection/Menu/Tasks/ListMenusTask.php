<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Data\Repositories\MenuRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListMenusTask extends ParentTask
{
    public function __construct(
        private readonly MenuRepository $repository,
    ) {
    }

    public function run(int $limit = 15): LengthAwarePaginator
    {
        $limit = $limit > 0 ? $limit : 15;

        return $this->repository
            ->addRequestCriteria()
            ->paginate($limit);
    }
}
