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

    public function run(
        string $type,
        int $revisionableId,
        ?int $perPage = null,
        ?int $page = null,
        ?string $order = null
    ): LengthAwarePaginator {
        $revisionableType = $this->revisionableResolver->resolve($type);
        if (! $revisionableType) {
            throw ValidationException::withMessages([
                'type' => ['Unsupported revisionable type.'],
            ]);
        }

        $defaultPerPage = (int) config('revision.default_per_page', 20);
        $maxPerPage = (int) config('revision.max_per_page', 200);

        $perPage = $perPage ?: $defaultPerPage;
        $perPage = max(1, min($perPage, $maxPerPage));
        $page = $page && $page > 0 ? $page : 1;
        $order = strtolower((string) $order) === 'asc' ? 'asc' : 'desc';

        return $this->listRevisionsTask->run($revisionableType, $revisionableId, $perPage, $page, $order);
    }
}
