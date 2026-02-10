<?php

namespace App\Containers\AppSection\Page\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\CustomField\Supports\CustomFieldService;
use App\Containers\AppSection\Page\Events\PageUpdatedEvent;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Tasks\FindPageTask;
use App\Containers\AppSection\Page\Tasks\UpdatePageTask;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdatePageAction extends ParentAction
{
    public function __construct(
        private readonly FindPageTask $findPageTask,
        private readonly UpdatePageTask $updatePageTask,
        private readonly SlugHelper $slugHelper,
        private readonly CustomFieldService $customFieldService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $seoMeta
     * @param array<int, mixed>|string|null $customFields
     */
    public function run(
        int $id,
        array $data,
        ?string $slug = null,
        ?array $seoMeta = null,
        array|string|null $customFields = null
    ): Page {
        $page = $data === []
            ? $this->findPageTask->run($id)
            : $this->updatePageTask->run($id, $data);

        if ($slug !== null) {
            $slug = trim($slug);
            $this->slugHelper->createSlug($page, $slug === '' ? null : $slug);
        }

        if ($seoMeta !== null) {
            $page->setMeta('seo_meta', $seoMeta);
        }

        if ($customFields !== null) {
            $this->customFieldService->saveCustomFieldsForModel($page, $customFields);
        }

        AuditLogRecorder::recordModel('updated', $page);

        PageUpdatedEvent::dispatch($page);

        return $page->refresh()->load(['slugable', 'user']);
    }
}
