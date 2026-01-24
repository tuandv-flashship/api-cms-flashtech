<?php

namespace App\Containers\AppSection\RequestLog\Tasks;

use App\Containers\AppSection\RequestLog\Models\RequestLog;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteRequestLogTask extends ParentTask
{
    public function run(int $id): void
    {
        RequestLog::query()->whereKey($id)->delete();
    }
}
