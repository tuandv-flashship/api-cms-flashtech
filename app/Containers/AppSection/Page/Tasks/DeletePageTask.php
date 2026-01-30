<?php

namespace App\Containers\AppSection\Page\Tasks;

use App\Containers\AppSection\Page\Data\Repositories\PageRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeletePageTask extends ParentTask
{
    public function __construct(
        private readonly PageRepository $repository,
    ) {
    }

    public function run(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
