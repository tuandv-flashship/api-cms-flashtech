<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tasks\CreateTagTask;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreateTagAction extends ParentAction
{
    public function __construct(
        private readonly CreateTagTask $createTagTask,
        private readonly SlugHelper $slugHelper,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed>|null $meta
     */
    public function run(array $data, ?string $slug = null, ?array $meta = null): Tag
    {
        $tag = $this->createTagTask->run($data);

        if ($slug !== null) {
            $slug = trim($slug);
            $this->slugHelper->createSlug($tag, $slug === '' ? null : $slug);
        } else {
            $this->slugHelper->createSlug($tag);
        }

        if ($meta) {
            foreach ($meta as $key => $value) {
                $tag->setMeta((string) $key, $value);
            }
        }

        AuditLogRecorder::recordModel('created', $tag);

        return $tag->refresh();
    }
}
