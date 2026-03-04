<?php

namespace App\Containers\AppSection\AdminMenu\Actions;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Containers\AppSection\AdminMenu\Tasks\FindAdminMenuItemTask;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\FindAdminMenuItemByIdRequest;
use App\Ship\Parents\Actions\Action as ParentAction;

final class FindAdminMenuItemByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindAdminMenuItemTask $findTask,
    ) {
    }

    public function run(FindAdminMenuItemByIdRequest $request): AdminMenuItem
    {
        return $this->findTask->run((int) $request->id, ['children.translations']);
    }
}
