<?php

namespace App\Containers\AppSection\AdminMenu\Actions;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Tasks\CreateAdminMenuItemTask;
use App\Containers\AppSection\AdminMenu\Tasks\FindAdminMenuItemTask;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\CreateAdminMenuItemRequest;
use App\Ship\Parents\Actions\Action as ParentAction;
use App\Containers\AppSection\AdminMenu\Supports\AdminMenu;

final class CreateAdminMenuItemAction extends ParentAction
{
    public function __construct(
        private readonly CreateAdminMenuItemTask $createTask,
        private readonly FindAdminMenuItemTask $findTask,
        private readonly AdminMenu $adminMenu,
    ) {
    }

    public function run(CreateAdminMenuItemRequest $request): AdminMenuItem
    {
        $validated = $request->validated();

        $this->createTask->run($validated);

        $this->adminMenu->flush();

        $item = AdminMenuItem::query()->where('key', $validated['key'])->firstOrFail();

        return $this->findTask->run((int) $item->getKey(), ['children.translations']);
    }
}
