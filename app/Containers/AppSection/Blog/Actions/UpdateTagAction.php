<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Events\TagUpdated;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tasks\FindTagTask;
use App\Containers\AppSection\Blog\Tasks\UpdateTagTask;
use App\Containers\AppSection\Blog\UI\API\Transporters\UpdateTagTransporter;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdateTagAction extends ParentAction
{
    public function __construct(
        private readonly FindTagTask $findTagTask,
        private readonly UpdateTagTask $updateTagTask,
        private readonly SlugHelper $slugHelper,
    ) {
    }

    /**
     * Update an existing tag with all related data
     */
    public function run(UpdateTagTransporter $data): Tag
    {
        return DB::transaction(function () use ($data) {
            $id = $data->getTagId();
            $tagData = $data->getTagData();

            $tag = $tagData === []
                ? $this->findTagTask->run($id)
                : $this->updateTagTask->run($id, $tagData);

            $slug = $data->getSlug();
            if ($slug !== null) {
                $slug = trim($slug);
                $this->slugHelper->createSlug($tag, $slug === '' ? null : $slug);
            }

            $seoMeta = $data->getSeoMeta();
            if ($seoMeta !== null) {
                $tag->setMeta('seo_meta', $seoMeta);
            }

            AuditLogRecorder::recordModel('updated', $tag);

            event(new TagUpdated($tag));

            return $tag->refresh();
        });
    }
}
