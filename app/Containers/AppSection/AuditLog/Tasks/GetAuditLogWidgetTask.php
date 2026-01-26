<?php

namespace App\Containers\AppSection\AuditLog\Tasks;

use App\Containers\AppSection\AuditLog\Models\AuditHistory;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\LengthAwarePaginator;

final class GetAuditLogWidgetTask extends ParentTask
{
    public function run(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return AuditHistory::query()
            ->with(['user', 'actor'])
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
