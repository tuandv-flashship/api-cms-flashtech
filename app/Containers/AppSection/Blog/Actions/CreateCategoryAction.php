<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Tasks\CreateCategoryTask;
use App\Containers\AppSection\CustomField\Supports\CustomFieldService;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreateCategoryAction extends ParentAction
{
    public function __construct(
        private readonly CreateCategoryTask $createCategoryTask,
        private readonly SlugHelper $slugHelper,
        private readonly CustomFieldService $customFieldService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $seoMeta
     * @param array<int, mixed>|string|null $customFields
     */
    public function run(array $data, ?string $slug = null, ?array $seoMeta = null, array|string|null $customFields = null): Category
    {
        if (! empty($data['is_default'])) {
            Category::query()->where('is_default', true)->update(['is_default' => false]);
        }

        if (! array_key_exists('parent_id', $data) || $data['parent_id'] === null) {
            $data['parent_id'] = 0;
        }

        $category = $this->createCategoryTask->run($data);

        if ($slug !== null) {
            $slug = trim($slug);
            $this->slugHelper->createSlug($category, $slug === '' ? null : $slug);
        } else {
            $this->slugHelper->createSlug($category);
        }

        if ($seoMeta !== null) {
            $category->setMeta('seo_meta', $seoMeta);
        }

        if ($customFields !== null) {
            $this->customFieldService->saveCustomFieldsForModel($category, $customFields);
        }

        AuditLogRecorder::recordModel('created', $category);

        return $category->refresh();
    }
}
