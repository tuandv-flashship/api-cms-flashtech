<?php

namespace App\Containers\AppSection\Gallery\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\Gallery\Models\GalleryMeta;
use App\Containers\AppSection\Gallery\Tasks\FindGalleryTask;
use App\Containers\AppSection\Gallery\Tasks\UpdateGalleryTask;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateGalleryAction extends ParentAction
{
    public function __construct(
        private readonly FindGalleryTask $findGalleryTask,
        private readonly UpdateGalleryTask $updateGalleryTask,
        private readonly SlugHelper $slugHelper,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, array<string, mixed>>|string|null $gallery
     * @param array<string, mixed>|null $seoMeta
     */
    public function run(int $id, array $data, ?string $slug = null, array|string|null $gallery = null, ?array $seoMeta = null): Gallery
    {
        $galleryModel = $data === []
            ? $this->findGalleryTask->run($id)
            : $this->updateGalleryTask->run($id, $data);

        if ($slug !== null) {
            $slug = trim($slug);
            $this->slugHelper->createSlug($galleryModel, $slug === '' ? null : $slug);
        }

        if ($seoMeta !== null) {
            $galleryModel->setMeta('seo_meta', $seoMeta);
        }

        $normalizedGallery = $this->normalizeGallery($gallery);
        if ($normalizedGallery !== null) {
            GalleryMeta::query()->updateOrCreate(
                [
                    'reference_id' => $galleryModel->getKey(),
                    'reference_type' => Gallery::class,
                ],
                ['images' => $normalizedGallery]
            );
        }

        AuditLogRecorder::recordModel('updated', $galleryModel);

        return $galleryModel->refresh()->load(['meta', 'slugable']);
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
}
