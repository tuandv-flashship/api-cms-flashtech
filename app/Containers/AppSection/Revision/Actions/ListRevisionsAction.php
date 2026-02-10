<?php

namespace App\Containers\AppSection\Revision\Actions;

use App\Containers\AppSection\Revision\Supports\RevisionableResolver;
use App\Containers\AppSection\Revision\Tasks\ListRevisionsTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

final class ListRevisionsAction extends ParentAction
{
    public function __construct(
        private readonly ListRevisionsTask $listRevisionsTask,
        private readonly RevisionableResolver $revisionableResolver,
    ) {
    }

    public function run(string $type, int $revisionableId): LengthAwarePaginator
    {
        $revisionableType = $this->revisionableResolver->resolveType($type);
        if (! $revisionableType) {
            throw ValidationException::withMessages([
                'type' => ['Unsupported revisionable type.'],
            ]);
        }

        return $this->listRevisionsTask->run($revisionableType, $revisionableId);
    }
}
