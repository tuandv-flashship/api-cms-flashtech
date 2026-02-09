<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Events\CategoryCreated;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Supports\CategorySupport;
use App\Containers\AppSection\Blog\Tasks\CreateCategoryTask;
use App\Containers\AppSection\Blog\UI\API\Transporters\CreateCategoryTransporter;
use App\Containers\AppSection\CustomField\Supports\CustomFieldService;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateCategoryAction extends ParentAction
{
    public function __construct(
        private readonly CreateCategoryTask $createCategoryTask,
        private readonly SlugHelper $slugHelper,
        private readonly CustomFieldService $customFieldService,
    ) {
    }

    /**
     * Create a new category with all related data
     */
    public function run(CreateCategoryTransporter $data): Category
    {
        return DB::transaction(function () use ($data) {
            $categoryData = $data->getCategoryData();
            
            CategorySupport::resetDefaultCategoryIfRequested($categoryData);

            if (! array_key_exists('parent_id', $categoryData) || $categoryData['parent_id'] === null) {
                $categoryData['parent_id'] = 0;
            }

            $category = $this->createCategoryTask->run($categoryData);

            $slug = $data->getSlug();
            if ($slug !== null) {
                $slug = trim($slug);
                $this->slugHelper->createSlug($category, $slug === '' ? null : $slug);
            } else {
                $this->slugHelper->createSlug($category);
            }

            $seoMeta = $data->getSeoMeta();
            if ($seoMeta !== null) {
                $category->setMeta('seo_meta', $seoMeta);
            }

            $customFields = $data->getCustomFields();
            if ($customFields !== null) {
                $this->customFieldService->saveCustomFieldsForModel($category, $customFields);
            }

            AuditLogRecorder::recordModel('created', $category);

            event(new CategoryCreated($category));

            return $category->refresh();
        });
    }
}
