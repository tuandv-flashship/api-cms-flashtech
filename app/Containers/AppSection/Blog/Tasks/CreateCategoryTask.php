<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Data\Repositories\CategoryRepository;
use App\Containers\AppSection\Blog\Models\Category;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateCategoryTask extends ParentTask
{
    public function __construct(
        private readonly CategoryRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(array $data): Category
    {
        return $this->repository->create($data);
    }
}
