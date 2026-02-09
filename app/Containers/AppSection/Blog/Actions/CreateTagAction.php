<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Events\TagCreated;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tasks\CreateTagTask;
use App\Containers\AppSection\Blog\UI\API\Transporters\CreateTagTransporter;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class CreateTagAction extends ParentAction
{
    public function __construct(
        private readonly CreateTagTask $createTagTask,
        private readonly SlugHelper $slugHelper,
    ) {
    }

    /**
     * Create a new tag with all related data
     */
    public function run(CreateTagTransporter $data): Tag
    {
        return DB::transaction(function () use ($data) {
            $tag = $this->createTagTask->run($data->getTagData());

            $slug = $data->getSlug();
            if ($slug !== null) {
                $slug = trim($slug);
                $this->slugHelper->createSlug($tag, $slug === '' ? null : $slug);
            } else {
                $this->slugHelper->createSlug($tag);
            }

            $seoMeta = $data->getSeoMeta();
            if ($seoMeta !== null) {
                $tag->setMeta('seo_meta', $seoMeta);
            }

            AuditLogRecorder::recordModel('created', $tag);

            event(new TagCreated($tag));

            return $tag->refresh();
        });
    }
}
