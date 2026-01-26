<?php

namespace App\Containers\AppSection\AuditLog\Tasks;

use App\Containers\AppSection\AuditLog\Models\AuditHistory;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteAllAuditLogsTask extends ParentTask
{
    public function run(): void
    {
        AuditHistory::query()->truncate();
    }
}
