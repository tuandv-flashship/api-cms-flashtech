<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Events\PostDeleted;
use App\Containers\AppSection\Blog\Tasks\DeletePostTask;
use App\Containers\AppSection\Blog\Tasks\FindPostTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeletePostAction extends ParentAction
{
    public function __construct(
        private readonly FindPostTask $findPostTask,
        private readonly DeletePostTask $deletePostTask,
    ) {
    }

    public function run(int $id): bool
    {
        $post = $this->findPostTask->run($id);
        $postId = $post->id;
        $postName = $post->name;

        $deleted = $this->deletePostTask->run($id);

        if ($deleted) {
            AuditLogRecorder::recordModel('deleted', $post);
            event(new PostDeleted($postId, $postName));
        }

        return $deleted;
    }
}
