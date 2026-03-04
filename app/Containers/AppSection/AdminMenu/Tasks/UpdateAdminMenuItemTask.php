<?php

namespace App\Containers\AppSection\AdminMenu\Tasks;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateAdminMenuItemTask extends ParentTask
{
    /**
     * @param array<string, mixed> $data
     */
    public function run(int $id, array $data): AdminMenuItem
    {
        $item = AdminMenuItem::query()->findOrFail($id);
        $item->update($data);

        return $item;
    }
}
