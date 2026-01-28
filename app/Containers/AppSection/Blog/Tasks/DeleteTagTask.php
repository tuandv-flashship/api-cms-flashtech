<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Data\Repositories\TagRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteTagTask extends ParentTask
{
    public function __construct(
        private readonly TagRepository $repository,
    ) {
    }

    public function run(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
