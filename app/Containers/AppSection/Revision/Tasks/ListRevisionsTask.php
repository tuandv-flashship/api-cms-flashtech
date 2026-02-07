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
        string $order = 'desc'
    ): LengthAwarePaginator {
        $defaultPerPage = (int) config('repository.pagination.limit', 10);
        $maxPerPage = (int) config('revision.max_per_page', 200);
        $perPage = max(1, min((int) request()->input('limit', $defaultPerPage), $maxPerPage));
        $page = max(1, (int) request()->input('page', 1));

        return Revision::query()
            ->with('user')
            ->where('revisionable_type', $revisionableType)
            ->where('revisionable_id', $revisionableId)
            ->orderBy('id', $order)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
