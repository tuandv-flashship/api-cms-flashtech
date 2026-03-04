<?php

namespace App\Containers\AppSection\AdminMenu\Tasks;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class RestoreAdminMenuItemTask extends ParentTask
{
    public function run(int $id): AdminMenuItem
    {
        $item = AdminMenuItem::withTrashed()->findOrFail($id);
        $item->restore();

        return $item;
    }
}
