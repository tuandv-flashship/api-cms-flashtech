<?php

namespace App\Containers\AppSection\AdminMenu\Tasks;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteAdminMenuItemTask extends ParentTask
{
    public function run(int $id): void
    {
        $item = AdminMenuItem::query()->findOrFail($id);
        $item->delete(); // Soft-delete
    }
}
