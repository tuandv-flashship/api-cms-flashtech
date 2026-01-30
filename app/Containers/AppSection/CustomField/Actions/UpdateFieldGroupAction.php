<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Tasks\UpdateFieldGroupTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateFieldGroupAction extends ParentAction
{
    public function __construct(
        private readonly UpdateFieldGroupTask $updateFieldGroupTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(int $id, array $data): FieldGroup
    {
        return $this->updateFieldGroupTask->run($id, $data);
    }
}
