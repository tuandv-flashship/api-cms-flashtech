<?php

namespace App\Containers\AppSection\Gallery\Actions;

use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\Gallery\Models\GalleryMeta;
use App\Containers\AppSection\Gallery\Tasks\FindGalleryTask;
use App\Containers\AppSection\LanguageAdvanced\Actions\UpdateSlugTranslationAction;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateGalleryTranslationAction extends ParentAction
{
    public function __construct(
        private readonly FindGalleryTask $findGalleryTask,
        private readonly UpdateSlugTranslationAction $updateSlugTranslationAction,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, array<string, mixed>>|string|null $gallery
     */
    public function run(
        int $id,
        array $data,
        string $langCode,
        ?string $slug = null,
        array|string|null $gallery = null,
        ?array $seoMeta = null
    ): Gallery {
        $galleryModel = $this->findGalleryTask->run($id, ['meta', 'slugable']);

        LanguageAdvancedManager::saveTranslation($galleryModel, $data, $langCode);

        if ($seoMeta !== null) {
            $galleryModel->setMeta($this->buildSeoMetaKey($langCode), $seoMeta);
        }

        $normalizedGallery = $this->normalizeGallery($gallery);
        if ($normalizedGallery !== null) {
            $meta = $galleryModel->meta;
            if (! $meta) {
                $meta = new GalleryMeta();
                $meta->reference_id = $galleryModel->getKey();
                $meta->reference_type = Gallery::class;
                $meta->images = [];
                $meta->save();
            }

            $images = json_encode($normalizedGallery);
            if ($images === false) {
                $images = '[]';
            }

            LanguageAdvancedManager::saveTranslation($meta, ['images' => $images], $langCode);
        }

        $slugKey = $this->resolveSlugKey($slug, $data['name'] ?? null);
        $slugId = $galleryModel->slugable?->getKey();
        if ($slugKey !== null && $slugId) {
            $this->updateSlugTranslationAction->run((int) $slugId, $langCode, $slugKey, Gallery::class);
        }

        LanguageAdvancedManager::setTranslationLocale($langCode);

        $galleryModel->load(LanguageAdvancedManager::withTranslations(['slugable', 'meta'], Gallery::class, $langCode));
        $galleryModel->loadMissing([
            'meta.translations' => static fn ($query) => $query->where('lang_code', $langCode),
            'slugable.translations' => static fn ($query) => $query->where('lang_code', $langCode),
        ]);

        return $galleryModel;
    }

    private function buildSeoMetaKey(string $langCode): string
    {
        return $langCode . '_seo_meta';
    }

    private function resolveSlugKey(?string $slug, ?string $fallbackName): ?string
    {
        $slug = trim((string) ($slug ?? $fallbackName ?? ''));

        return $slug !== '' ? $slug : null;
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
