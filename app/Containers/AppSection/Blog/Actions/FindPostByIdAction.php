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

    public function run(int $id, bool $includeAuthor = false): Post
    {
        $with = ['categories.slugable', 'tags.slugable', 'slugable', 'galleryMeta'];

        if ($includeAuthor) {
            $with[] = 'author';
        }

        return $this->findPostTask->run($id, $with);
    }
}
