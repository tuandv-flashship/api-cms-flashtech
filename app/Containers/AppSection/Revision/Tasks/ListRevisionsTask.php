<?php

namespace App\Containers\AppSection\Revision\Tasks;

use App\Containers\AppSection\Revision\Models\Revision;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListRevisionsTask extends ParentTask
{
    public function run(
        string $revisionableType,
        int $revisionableId,
        int $perPage,
        int $page,
        string $order = 'desc'
    ): LengthAwarePaginator {
        return Revision::query()
            ->with('user')
            ->where('revisionable_type', $revisionableType)
            ->where('revisionable_id', $revisionableId)
            ->orderBy('id', $order)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
