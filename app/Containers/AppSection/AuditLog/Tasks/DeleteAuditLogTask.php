<?php

namespace App\Containers\AppSection\AuditLog\Tasks;

use App\Containers\AppSection\AuditLog\Models\AuditHistory;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteAuditLogTask extends ParentTask
{
    public function run(int $id): void
    {
        AuditHistory::query()->whereKey($id)->delete();
    }
}
