<?php

namespace App\Containers\AppSection\CustomField\Tasks;

use App\Containers\AppSection\CustomField\Supports\FieldGroupManager;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteFieldGroupTask extends ParentTask
{
    public function __construct(
        private readonly FieldGroupManager $fieldGroupManager,
    ) {
    }

    public function run(int $id): bool
    {
        return $this->fieldGroupManager->delete($id);
    }
}
