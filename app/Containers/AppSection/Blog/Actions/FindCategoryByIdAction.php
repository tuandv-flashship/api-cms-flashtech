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

    public function run(int $id, bool $includeParent = false, bool $includeChildren = false): Category
    {
        $with = ['slugable'];

        if ($includeParent) {
            $with[] = 'parent';
            $with[] = 'parent.slugable';
        }

        if ($includeChildren) {
            $with[] = 'children';
            $with[] = 'children.slugable';
        }

        return $this->findCategoryTask->run($id, $with);
    }
}
