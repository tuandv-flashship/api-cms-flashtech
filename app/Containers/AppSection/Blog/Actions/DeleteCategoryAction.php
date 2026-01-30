<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Events\CategoryDeleted;
use App\Containers\AppSection\Blog\Tasks\DeleteCategoryTask;
use App\Containers\AppSection\Blog\Tasks\FindCategoryTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeleteCategoryAction extends ParentAction
{
    public function __construct(
        private readonly FindCategoryTask $findCategoryTask,
        private readonly DeleteCategoryTask $deleteCategoryTask,
    ) {
    }

    public function run(int $id): bool
    {
        $category = $this->findCategoryTask->run($id);
        $categoryId = $category->id;
        $categoryName = $category->name;

        $deleted = $this->deleteCategoryTask->run($id);

        if ($deleted) {
            AuditLogRecorder::recordModel('deleted', $category);
            event(new CategoryDeleted($categoryId, $categoryName));
        }

        return $deleted;
    }
}
