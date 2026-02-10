<?php

namespace App\Containers\AppSection\Gallery\Actions;

use App\Containers\AppSection\Gallery\Tasks\ListGalleriesTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListGalleriesAction extends ParentAction
{
    public function __construct(
        private readonly ListGalleriesTask $listGalleriesTask,
    ) {
    }

    public function run(): LengthAwarePaginator
    {
        return $this->listGalleriesTask->run();
    }
}
