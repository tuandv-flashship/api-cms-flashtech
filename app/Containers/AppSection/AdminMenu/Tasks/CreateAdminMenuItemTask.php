<?php

namespace App\Containers\AppSection\AdminMenu\Tasks;

use App\Containers\AppSection\AdminMenu\Models\AdminMenuItem;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateAdminMenuItemTask extends ParentTask
{
    /**
     * @param array<string, mixed> $data
     */
    public function run(array $data): AdminMenuItem
    {
        return AdminMenuItem::query()->create($data);
    }
}
