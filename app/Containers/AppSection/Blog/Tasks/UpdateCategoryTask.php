<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Data\Repositories\CategoryRepository;
use App\Containers\AppSection\Blog\Models\Category;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateCategoryTask extends ParentTask
{
    public function __construct(
        private readonly CategoryRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(int $id, array $data): Category
    {
        return $this->repository->update($data, $id);
    }
}
