<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Data\Repositories\PostRepository;
use App\Containers\AppSection\Blog\Models\Post;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreatePostTask extends ParentTask
{
    public function __construct(
        private readonly PostRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(array $data): Post
    {
        return $this->repository->create($data);
    }
}
