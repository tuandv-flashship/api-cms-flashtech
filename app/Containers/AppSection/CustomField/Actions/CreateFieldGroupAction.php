<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Tasks\CreateFieldGroupTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreateFieldGroupAction extends ParentAction
{
    public function __construct(
        private readonly CreateFieldGroupTask $createFieldGroupTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(array $data): FieldGroup
    {
        return $this->createFieldGroupTask->run($data);
    }
}
