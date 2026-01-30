<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Events\TagUpdated;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tasks\FindTagTask;
use App\Containers\AppSection\Blog\Tasks\UpdateTagTask;
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
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $seoMeta
     */
    public function run(int $id, array $data, ?string $slug = null, ?array $seoMeta = null): Tag
    {
        return DB::transaction(function () use ($id, $data, $slug, $seoMeta) {
            $tag = $data === []
                ? $this->findTagTask->run($id)
                : $this->updateTagTask->run($id, $data);

            if ($slug !== null) {
                $slug = trim($slug);
                $this->slugHelper->createSlug($tag, $slug === '' ? null : $slug);
            }

            if ($seoMeta !== null) {
                $tag->setMeta('seo_meta', $seoMeta);
            }

            AuditLogRecorder::recordModel('updated', $tag);

            event(new TagUpdated($tag));

            return $tag->refresh();
        });
    }
}
