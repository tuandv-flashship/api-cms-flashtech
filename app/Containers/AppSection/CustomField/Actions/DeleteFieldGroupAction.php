<?php

namespace App\Containers\AppSection\CustomField\Actions;

use App\Containers\AppSection\CustomField\Tasks\DeleteFieldGroupTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeleteFieldGroupAction extends ParentAction
{
    public function __construct(
        private readonly DeleteFieldGroupTask $deleteFieldGroupTask,
    ) {
    }

    public function run(int $id): bool
    {
        return $this->deleteFieldGroupTask->run($id);
    }
}
