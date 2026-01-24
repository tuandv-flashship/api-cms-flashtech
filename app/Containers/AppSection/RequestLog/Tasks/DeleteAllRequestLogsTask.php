<?php

namespace App\Containers\AppSection\RequestLog\Tasks;

use App\Containers\AppSection\RequestLog\Models\RequestLog;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteAllRequestLogsTask extends ParentTask
{
    public function run(): void
    {
        RequestLog::query()->truncate();
    }
}
