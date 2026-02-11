<?php

namespace App\Containers\AppSection\Menu\Tasks;

use App\Containers\AppSection\Menu\Data\Repositories\MenuRepository;
use App\Containers\AppSection\Menu\Models\Menu;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateMenuTask extends ParentTask
{
    public function __construct(
        private readonly MenuRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(array $data): Menu
    {
        return $this->repository->create($data);
    }
}
