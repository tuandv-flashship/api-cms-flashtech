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
use App\Containers\AppSection\Blog\UI\API\Transporters\UpdatePostTransporter;
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
     * Update an existing post with all related data
     */
    public function run(UpdatePostTransporter $data): Post
    {
        return DB::transaction(function () use ($data) {
            $id = $data->getPostId();
            $postData = $data->getPostData();
            
            $existingPost = $this->findPostTask->run($id);
            $previousStatus = $existingPost->status;

            $post = $postData === []
                ? $existingPost
                : $this->updatePostTask->run($id, $postData);

            $categoryIds = $data->getCategoryIds();
            if ($categoryIds !== null) {
                $post->categories()->sync($categoryIds);
            }

            $tagIds = TagResolver::resolveTagIds($data->getTagIds(), $data->getTagNames());
            if ($tagIds !== null) {
                $post->tags()->sync($tagIds);
            }

            $slug = $data->getSlug();
            if ($slug !== null) {
                $slug = trim($slug);
                $this->slugHelper->createSlug($post, $slug === '' ? null : $slug);
            }

            $normalizedGallery = GalleryNormalizer::normalize($data->getGallery());
            if ($normalizedGallery !== null) {
                GalleryMeta::query()->updateOrCreate(
                    [
                        'reference_id' => $post->getKey(),
                        'reference_type' => Post::class,
                    ],
                    ['images' => $normalizedGallery]
                );
            }

            $seoMeta = $data->getSeoMeta();
            if ($seoMeta !== null) {
                $post->setMeta('seo_meta', $seoMeta);
            }

            $customFields = $data->getCustomFields();
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
