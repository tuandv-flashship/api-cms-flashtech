<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tasks\FindTagTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class FindTagByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindTagTask $findTagTask,
    ) {
    }

    public function run(int $id): Tag
    {
        return $this->findTagTask->run($id, ['slugable']);
    }
}
