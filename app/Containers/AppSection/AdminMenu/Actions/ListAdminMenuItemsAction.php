<?php

namespace App\Containers\AppSection\AdminMenu\Actions;

use App\Containers\AppSection\AdminMenu\Tasks\BuildAdminMenuTreeTask;
use App\Containers\AppSection\AdminMenu\Tasks\ListAdminMenuItemsTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ListAdminMenuItemsAction extends ParentAction
{
    public function __construct(
        private readonly ListAdminMenuItemsTask $listTask,
        private readonly BuildAdminMenuTreeTask $buildTreeTask,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(): array
    {
        $items = $this->listTask->run(activeOnly: false);

        return $this->buildTreeTask->run($items);
    }
}
