<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Tasks\ListCategoriesTreeTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ListCategoriesTreeAction extends ParentAction
{
    public function __construct(
        private readonly ListCategoriesTreeTask $listCategoriesTreeTask,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(?string $status = null): array
    {
        return $this->listCategoriesTreeTask->run($status);
    }
}
