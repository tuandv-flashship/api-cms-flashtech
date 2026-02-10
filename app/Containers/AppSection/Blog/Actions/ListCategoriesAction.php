<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Tasks\ListCategoriesTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListCategoriesAction extends ParentAction
{
    public function __construct(
        private readonly ListCategoriesTask $listCategoriesTask,
    ) {
    }

    public function run(): LengthAwarePaginator
    {
        return $this->listCategoriesTask->run();
    }
}
