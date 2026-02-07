<?php

namespace App\Containers\AppSection\Page\Actions;

use App\Containers\AppSection\Page\Tasks\ListPagesTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPagesAction extends ParentAction
{
    public function __construct(
        private readonly ListPagesTask $listPagesTask,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters): LengthAwarePaginator
    {
        return $this->listPagesTask->run($filters);
    }
}
