<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Tasks\FindCategoryTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class FindCategoryByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindCategoryTask $findCategoryTask,
    ) {
    }

    public function run(int $id): Category
    {
        return $this->findCategoryTask->run($id, ['slugable', 'parent']);
    }
}
