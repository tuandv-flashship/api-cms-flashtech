<?php

namespace App\Containers\AppSection\Authorization\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Authorization\Models\Role;
use App\Containers\AppSection\Authorization\Tasks\FindRoleTask;
use App\Containers\AppSection\Authorization\Tasks\UpdateRoleTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateRoleWithPermissionsAction extends ParentAction
{
    public function __construct(
        private readonly FindRoleTask $findRoleTask,
        private readonly UpdateRoleTask $updateRoleTask,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param int[]|null $permissionIds
     */
    public function run(int $roleId, array $data, ?array $permissionIds): Role
    {
        $shouldLog = $data !== [] || $permissionIds !== null;

        $role = $data === []
            ? $this->findRoleTask->run($roleId)
            : $this->updateRoleTask->run($roleId, $data);

        if ($permissionIds !== null) {
            $role->syncPermissions($permissionIds);
            $role = $role->refresh();
        }

        if ($shouldLog) {
            AuditLogRecorder::recordModel('updated', $role);
        }

        return $role;
    }
}
