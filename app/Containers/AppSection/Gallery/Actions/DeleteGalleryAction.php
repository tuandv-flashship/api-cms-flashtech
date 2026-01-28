<?php

namespace App\Containers\AppSection\Gallery\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Gallery\Tasks\DeleteGalleryTask;
use App\Containers\AppSection\Gallery\Tasks\FindGalleryTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeleteGalleryAction extends ParentAction
{
    public function __construct(
        private readonly FindGalleryTask $findGalleryTask,
        private readonly DeleteGalleryTask $deleteGalleryTask,
    ) {
    }

    public function run(int $id): bool
    {
        $gallery = $this->findGalleryTask->run($id);

        $result = $this->deleteGalleryTask->run($id);

        AuditLogRecorder::recordModel('deleted', $gallery);

        return $result;
    }
}
