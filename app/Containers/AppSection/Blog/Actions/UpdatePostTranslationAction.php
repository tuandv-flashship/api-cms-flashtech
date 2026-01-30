<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Tasks\FindPostTask;
use App\Containers\AppSection\CustomField\Supports\CustomFieldService;
use App\Containers\AppSection\Gallery\Models\GalleryMeta;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\LanguageAdvanced\Actions\UpdateSlugTranslationAction;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdatePostTranslationAction extends ParentAction
{
    public function __construct(
        private readonly FindPostTask $findPostTask,
        private readonly UpdateSlugTranslationAction $updateSlugTranslationAction,
        private readonly CustomFieldService $customFieldService,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(
        int $id,
        array $data,
        string $langCode,
        ?string $slug = null,
        ?array $seoMeta = null,
        array|string|null $gallery = null,
        array|string|null $customFields = null
    ): Post
    {
        $post = $this->findPostTask->run($id, ['galleryMeta', 'slugable']);

        LanguageAdvancedManager::saveTranslation($post, $data, $langCode);

        if ($seoMeta !== null) {
            $post->setMeta($this->buildSeoMetaKey($langCode), $seoMeta);
        }

        if ($customFields !== null) {
            $this->customFieldService->saveCustomFieldsForModel($post, $customFields, $langCode);
        }

        $normalizedGallery = $this->normalizeGallery($gallery);
        if ($normalizedGallery !== null) {
            $meta = $post->galleryMeta;
            if (! $meta) {
                $meta = new GalleryMeta();
                $meta->reference_id = $post->getKey();
                $meta->reference_type = Post::class;
                $meta->images = [];
                $meta->save();
            }

            LanguageAdvancedManager::saveTranslation($meta, ['images' => $normalizedGallery], $langCode);
        }

        $slugKey = $this->resolveSlugKey($slug, $data['name'] ?? null);
        $slugId = $post->slugable?->getKey();
        if ($slugKey !== null && $slugId) {
            $this->updateSlugTranslationAction->run((int) $slugId, $langCode, $slugKey, Post::class);
        }

        LanguageAdvancedManager::setTranslationLocale($langCode);

        $post->load(LanguageAdvancedManager::withTranslations(['slugable', 'galleryMeta'], Post::class, $langCode));
        $post->loadMissing([
            'galleryMeta.translations' => static fn ($query) => $query->where('lang_code', $langCode),
            'slugable.translations' => static fn ($query) => $query->where('lang_code', $langCode),
        ]);

        return $post;
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
