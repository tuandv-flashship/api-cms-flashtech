<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Events\TagDeleted;
use App\Containers\AppSection\Blog\Tasks\DeleteTagTask;
use App\Containers\AppSection\Blog\Tasks\FindTagTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeleteTagAction extends ParentAction
{
    public function __construct(
        private readonly FindTagTask $findTagTask,
        private readonly DeleteTagTask $deleteTagTask,
    ) {
    }

    public function run(int $id): bool
    {
        $tag = $this->findTagTask->run($id);
        $tagId = $tag->id;
        $tagName = $tag->name;

        $deleted = $this->deleteTagTask->run($id);

        if ($deleted) {
            AuditLogRecorder::recordModel('deleted', $tag);
            event(new TagDeleted($tagId, $tagName));
        }

        return $deleted;
    }
}
