<?php

namespace App\Containers\AppSection\AdminMenu\Actions;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Tasks\FindAdminMenuItemTask;
use App\Containers\AppSection\AdminMenu\Tasks\UpdateAdminMenuItemTask;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\UpdateAdminMenuItemRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use App\Containers\AppSection\AdminMenu\Supports\AdminMenu;

final class UpdateAdminMenuItemAction extends ParentAction
{
    public function __construct(
        private readonly UpdateAdminMenuItemTask $updateTask,
        private readonly FindAdminMenuItemTask $findTask,
        private readonly AdminMenu $adminMenu,
    ) {
    }

    public function run(UpdateAdminMenuItemRequest $request): AdminMenuItem
    {
        $this->updateTask->run((int) $request->id, $request->validated());

        $this->adminMenu->flush();

        return $this->findTask->run((int) $request->id, ['children.translations']);
    }
}
