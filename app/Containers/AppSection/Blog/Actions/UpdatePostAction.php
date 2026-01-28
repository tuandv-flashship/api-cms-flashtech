<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tasks\FindPostTask;
use App\Containers\AppSection\Blog\Tasks\UpdatePostTask;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdatePostAction extends ParentAction
{
    public function __construct(
        private readonly FindPostTask $findPostTask,
        private readonly UpdatePostTask $updatePostTask,
        private readonly SlugHelper $slugHelper,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param int[]|null $categoryIds
     * @param int[]|null $tagIds
     * @param string[]|null $tagNames
     * @param array<string, mixed>|null $seoMeta
     */
    public function run(
        int $id,
        array $data,
        ?array $categoryIds = null,
        ?array $tagIds = null,
        ?array $tagNames = null,
        ?string $slug = null,
        ?array $seoMeta = null
    ): Post {
        $post = $data === []
            ? $this->findPostTask->run($id)
            : $this->updatePostTask->run($id, $data);

        if ($categoryIds !== null) {
            $post->categories()->sync($categoryIds);
        }

        $tagIds = $this->resolveTagIds($tagIds, $tagNames);
        if ($tagIds !== null) {
            $post->tags()->sync($tagIds);
        }

        if ($slug !== null) {
            $slug = trim($slug);
            $this->slugHelper->createSlug($post, $slug === '' ? null : $slug);
        }

        if ($seoMeta !== null) {
            $post->setMeta('seo_meta', $seoMeta);
        }

        AuditLogRecorder::recordModel('updated', $post);

        return $post->refresh();
    }

    /**
     * @param int[]|null $tagIds
     * @param string[]|null $tagNames
     * @return int[]|null
     */
    private function resolveTagIds(?array $tagIds, ?array $tagNames): ?array
    {
        $tagIds = $tagIds ?? [];
        $tagNames = $tagNames ?? [];

        foreach ($tagNames as $tagName) {
            $tagName = trim((string) $tagName);
            if ($tagName === '') {
                continue;
            }

            $tag = Tag::query()->firstOrCreate(
                ['name' => $tagName],
                ['status' => ContentStatus::PUBLISHED]
            );

            $tagIds[] = $tag->getKey();
        }

        $tagIds = array_values(array_unique(array_filter($tagIds)));

        return $tagIds !== [] ? $tagIds : null;
    }
}
