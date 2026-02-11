<?php

namespace App\Containers\AppSection\Menu\Actions;

use App\Containers\AppSection\Menu\Tasks\ListMenusTask;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\ListMenusRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListMenusAction extends ParentAction
{
    public function __construct(
        private readonly ListMenusTask $listMenusTask,
    ) {
    }

    public function run(ListMenusRequest $request): LengthAwarePaginator
    {
        $limit = (int) ($request->query('limit') ?? 15);

        return $this->listMenusTask->run($limit);
    }
}
