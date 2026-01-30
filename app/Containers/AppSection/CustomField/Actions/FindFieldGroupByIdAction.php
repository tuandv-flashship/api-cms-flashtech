<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Tasks\FindFieldGroupTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class FindFieldGroupByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindFieldGroupTask $findFieldGroupTask,
    ) {
    }

    public function run(int $id): FieldGroup
    {
        return $this->findFieldGroupTask->run($id);
    }
}
