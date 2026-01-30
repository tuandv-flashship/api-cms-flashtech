<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Events\PostPublished;
use App\Containers\AppSection\Blog\Events\PostUpdated;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Supports\GalleryNormalizer;
use App\Containers\AppSection\Blog\Supports\TagResolver;
use App\Containers\AppSection\Blog\Tasks\FindPostTask;
use App\Containers\AppSection\Blog\Tasks\UpdatePostTask;
use App\Containers\AppSection\CustomField\Supports\CustomFieldService;
use App\Containers\AppSection\Gallery\Models\GalleryMeta;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Support\Facades\DB;

final class UpdatePostAction extends ParentAction
{
    public function __construct(
        private readonly FindPostTask $findPostTask,
        private readonly UpdatePostTask $updatePostTask,
        private readonly SlugHelper $slugHelper,
        private readonly CustomFieldService $customFieldService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param int[]|null $categoryIds
     * @param int[]|null $tagIds
     * @param string[]|null $tagNames
     * @param array<int, array<string, mixed>>|string|null $gallery
     * @param array<string, mixed>|null $seoMeta
     * @param array<int, mixed>|string|null $customFields
     */
    public function run(
        int $id,
        array $data,
        ?array $categoryIds = null,
        ?array $tagIds = null,
        ?array $tagNames = null,
        ?string $slug = null,
        array|string|null $gallery = null,
        ?array $seoMeta = null,
        array|string|null $customFields = null
    ): Post {
        return DB::transaction(function () use ($id, $data, $categoryIds, $tagIds, $tagNames, $slug, $gallery, $seoMeta, $customFields) {
            $existingPost = $this->findPostTask->run($id);
            $previousStatus = $existingPost->status;

            $post = $data === []
                ? $existingPost
                : $this->updatePostTask->run($id, $data);

            if ($categoryIds !== null) {
                $post->categories()->sync($categoryIds);
            }

            $tagIds = TagResolver::resolve($tagIds, $tagNames);
            if ($tagIds !== null) {
                $post->tags()->sync($tagIds);
            }

            if ($slug !== null) {
                $slug = trim($slug);
                $this->slugHelper->createSlug($post, $slug === '' ? null : $slug);
            }

            $normalizedGallery = GalleryNormalizer::normalize($gallery);
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

            if ($customFields !== null) {
                $this->customFieldService->saveCustomFieldsForModel($post, $customFields);
            }

            AuditLogRecorder::recordModel('updated', $post);

            event(new PostUpdated($post));

            // Dispatch PostPublished if status changed to published
            if ($previousStatus !== ContentStatus::PUBLISHED && $post->status === ContentStatus::PUBLISHED) {
                event(new PostPublished($post, $previousStatus));
            }

            return $post->refresh();
        });
    }


}
