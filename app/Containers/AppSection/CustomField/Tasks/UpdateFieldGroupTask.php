<?php

namespace App\Containers\AppSection\CustomField\Tasks;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Supports\FieldGroupManager;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateFieldGroupTask extends ParentTask
{
    public function __construct(
        private readonly FieldGroupManager $fieldGroupManager,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(int $id, array $data): FieldGroup
    {
        return $this->fieldGroupManager->update($id, $data);
    }
}
