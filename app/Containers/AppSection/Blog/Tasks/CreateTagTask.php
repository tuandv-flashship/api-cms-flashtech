<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Data\Repositories\TagRepository;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateTagTask extends ParentTask
{
    public function __construct(
        private readonly TagRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(array $data): Tag
    {
        return $this->repository->create($data);
    }
}
