<?php

namespace App\Containers\AppSection\RequestLog\Tasks;

use App\Containers\AppSection\RequestLog\Models\RequestLog;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\LengthAwarePaginator;

final class GetRequestLogWidgetTask extends ParentTask
{
    public function run(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return RequestLog::query()
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
