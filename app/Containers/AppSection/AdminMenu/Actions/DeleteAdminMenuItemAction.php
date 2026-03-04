<?php

namespace App\Containers\AppSection\AdminMenu\Actions;

use App\Containers\AppSection\AdminMenu\Tasks\DeleteAdminMenuItemTask;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\DeleteAdminMenuItemRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use App\Containers\AppSection\AdminMenu\Supports\AdminMenu;

final class DeleteAdminMenuItemAction extends ParentAction
{
    public function __construct(
        private readonly DeleteAdminMenuItemTask $deleteTask,
        private readonly AdminMenu $adminMenu,
    ) {
    }

    public function run(DeleteAdminMenuItemRequest $request): void
    {
        $this->deleteTask->run((int) $request->id);

        $this->adminMenu->flush();
    }
}
