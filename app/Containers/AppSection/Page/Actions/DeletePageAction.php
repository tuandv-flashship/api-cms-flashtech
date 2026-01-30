<?php

namespace App\Containers\AppSection\Page\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Page\Tasks\DeletePageTask;
use App\Containers\AppSection\Page\Tasks\FindPageTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeletePageAction extends ParentAction
{
    public function __construct(
        private readonly FindPageTask $findPageTask,
        private readonly DeletePageTask $deletePageTask,
    ) {
    }

    public function run(int $id): bool
    {
        $page = $this->findPageTask->run($id);
        $deleted = $this->deletePageTask->run($id);

        if ($deleted) {
            AuditLogRecorder::recordModel('deleted', $page);
        }

        return $deleted;
    }
}
