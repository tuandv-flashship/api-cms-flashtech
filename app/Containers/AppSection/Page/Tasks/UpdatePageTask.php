<?php

namespace App\Containers\AppSection\Page\Tasks;

use App\Containers\AppSection\Page\Data\Repositories\PageRepository;
use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdatePageTask extends ParentTask
{
    public function __construct(
        private readonly PageRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(int $id, array $data): Page
    {
        return $this->repository->update($data, $id);
    }
}
