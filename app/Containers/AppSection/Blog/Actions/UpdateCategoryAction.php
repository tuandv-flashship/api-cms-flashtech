<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Tasks\FindCategoryTask;
use App\Containers\AppSection\Blog\Tasks\UpdateCategoryTask;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateCategoryAction extends ParentAction
{
    public function __construct(
        private readonly FindCategoryTask $findCategoryTask,
        private readonly UpdateCategoryTask $updateCategoryTask,
        private readonly SlugHelper $slugHelper,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $meta
     */
    public function run(int $id, array $data, ?string $slug = null, ?array $meta = null): Category
    {
        if (array_key_exists('is_default', $data) && (bool) $data['is_default']) {
            Category::query()->where('is_default', true)->update(['is_default' => false]);
        }

        $category = $data === []
            ? $this->findCategoryTask->run($id)
            : $this->updateCategoryTask->run($id, $data);

        if ($slug !== null) {
            $slug = trim($slug);
            $this->slugHelper->createSlug($category, $slug === '' ? null : $slug);
        }

        if ($meta) {
            foreach ($meta as $key => $value) {
                $category->setMeta((string) $key, $value);
            }
        }

        AuditLogRecorder::recordModel('updated', $category);

        return $category->refresh();
    }
}
