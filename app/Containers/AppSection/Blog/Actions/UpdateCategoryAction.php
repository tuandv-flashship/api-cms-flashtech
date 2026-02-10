<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Events\CategoryUpdated;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Supports\CategorySupport;
use App\Containers\AppSection\Blog\Tasks\FindCategoryTask;
use App\Containers\AppSection\Blog\Tasks\UpdateCategoryTask;
use App\Containers\AppSection\Blog\UI\API\Transporters\UpdateCategoryTransporter;
use App\Containers\AppSection\CustomField\Supports\CustomFieldService;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateCategoryAction extends ParentAction
{
    public function __construct(
        private readonly FindCategoryTask $findCategoryTask,
        private readonly UpdateCategoryTask $updateCategoryTask,
        private readonly SlugHelper $slugHelper,
        private readonly CustomFieldService $customFieldService,
    ) {
    }

    /**
     * Update an existing category with all related data
     */
    public function run(UpdateCategoryTransporter $data): Category
    {
        return DB::transaction(function () use ($data) {
            $id = $data->getCategoryId();
            $categoryData = $data->getCategoryData();

            CategorySupport::resetDefaultCategoryIfRequested($categoryData);

            if (array_key_exists('parent_id', $categoryData) && $categoryData['parent_id'] === null) {
                $categoryData['parent_id'] = 0;
            }

            $category = $categoryData === []
                ? $this->findCategoryTask->run($id)
                : $this->updateCategoryTask->run($id, $categoryData);

            $slug = $data->getSlug();
            if ($slug !== null) {
                $slug = trim($slug);
                $this->slugHelper->createSlug($category, $slug === '' ? null : $slug);
            }

            $seoMeta = $data->getSeoMeta();
            if ($seoMeta !== null) {
                $category->setMeta('seo_meta', $seoMeta);
            }

            $customFields = $data->getCustomFields();
            if ($customFields !== null) {
                $this->customFieldService->saveCustomFieldsForModel($category, $customFields);
            }

            AuditLogRecorder::recordModel('updated', $category);

            event(new CategoryUpdated($category));

            return $category->refresh();
        });
    }
}
