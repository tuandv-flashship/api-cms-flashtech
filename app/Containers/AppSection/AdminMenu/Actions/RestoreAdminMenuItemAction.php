<?php

namespace App\Containers\AppSection\AdminMenu\Actions;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Tasks\FindAdminMenuItemTask;
use App\Containers\AppSection\AdminMenu\Tasks\RestoreAdminMenuItemTask;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\RestoreAdminMenuItemRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use App\Containers\AppSection\AdminMenu\Supports\AdminMenu;

final class RestoreAdminMenuItemAction extends ParentAction
{
    public function __construct(
        private readonly RestoreAdminMenuItemTask $restoreTask,
        private readonly FindAdminMenuItemTask $findTask,
        private readonly AdminMenu $adminMenu,
    ) {
    }

    public function run(RestoreAdminMenuItemRequest $request): AdminMenuItem
    {
        $this->restoreTask->run((int) $request->id);

        $this->adminMenu->flush();

        return $this->findTask->run((int) $request->id, ['children.translations']);
    }
}
