<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Tasks\FindPostTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class FindPostByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindPostTask $findPostTask,
    ) {
    }

    public function run(int $id): Post
    {
        return $this->findPostTask->run($id, ['categories', 'tags', 'slugable', 'author', 'galleryMeta']);
    }
}
