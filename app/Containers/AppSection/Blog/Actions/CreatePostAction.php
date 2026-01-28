<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Tasks\CreatePostTask;
use App\Containers\AppSection\Gallery\Models\GalleryMeta;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreatePostAction extends ParentAction
{
    public function __construct(
        private readonly CreatePostTask $createPostTask,
        private readonly SlugHelper $slugHelper,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param int[]|null $categoryIds
     * @param int[]|null $tagIds
     * @param string[]|null $tagNames
     * @param array<int, array<string, mixed>>|string|null $gallery
     * @param array<string, mixed>|null $seoMeta
     */
    public function run(
        array $data,
        ?array $categoryIds = null,
        ?array $tagIds = null,
        ?array $tagNames = null,
        ?string $slug = null,
        array|string|null $gallery = null,
        ?array $seoMeta = null
    ): Post {
        $post = $this->createPostTask->run($data);

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
        } else {
            $this->slugHelper->createSlug($post);
        }

        $normalizedGallery = $this->normalizeGallery($gallery);
        if ($normalizedGallery !== null) {
            GalleryMeta::query()->updateOrCreate(
                [
                    'reference_id' => $post->getKey(),
                    'reference_type' => Post::class,
                ],
                ['images' => $normalizedGallery]
            );
        }

        if ($seoMeta !== null) {
            $post->setMeta('seo_meta', $seoMeta);
        }

        AuditLogRecorder::recordModel('created', $post);

        return $post->refresh();
    }

    /**
     * @param array<int, array<string, mixed>>|string|null $gallery
     * @return array<int, array<string, mixed>>|null
     */
    private function normalizeGallery(array|string|null $gallery): ?array
    {
        if ($gallery === null) {
            return null;
        }

        if (is_string($gallery)) {
            $decoded = json_decode($gallery, true);
            if (! is_array($decoded)) {
                return null;
            }
            $gallery = $decoded;
        }

        $items = [];
        foreach ($gallery as $item) {
            if (! is_array($item)) {
                continue;
            }

            $image = $item['img'] ?? $item['image'] ?? null;
            if (! is_string($image) || trim($image) === '') {
                continue;
            }

            $items[] = [
                'img' => $image,
                'description' => isset($item['description']) ? (string) $item['description'] : null,
            ];
        }

        return $items;
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
