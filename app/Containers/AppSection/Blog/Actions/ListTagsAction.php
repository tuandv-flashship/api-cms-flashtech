<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Tasks\ListTagsTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTagsAction extends ParentAction
{
    public function __construct(
        private readonly ListTagsTask $listTagsTask,
    ) {
    }

    public function run(): LengthAwarePaginator
    {
        return $this->listTagsTask->run();
    }
}
