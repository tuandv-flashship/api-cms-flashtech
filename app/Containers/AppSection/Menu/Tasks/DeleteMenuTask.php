<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Data\Repositories\MenuRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteMenuTask extends ParentTask
{
    public function __construct(
        private readonly MenuRepository $repository,
    ) {
    }

    public function run(int $id): bool
    {
        return (bool) $this->repository->delete($id);
    }
}
