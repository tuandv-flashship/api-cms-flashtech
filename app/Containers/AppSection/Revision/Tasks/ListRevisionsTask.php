<?php

namespace App\Containers\AppSection\Revision\Tasks;

use App\Containers\AppSection\Revision\Data\Repositories\RevisionRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListRevisionsTask extends ParentTask
{
    public function __construct(
        private readonly RevisionRepository $repository,
    ) {
    }

    public function run(string $revisionableType, int $revisionableId): LengthAwarePaginator
    {
        return $this->repository
            ->scope(static fn ($query) => $query
                ->with('user')
                ->where('revisionable_type', $revisionableType)
                ->where('revisionable_id', $revisionableId))
            ->addRequestCriteria()
            ->paginate();
    }
}
