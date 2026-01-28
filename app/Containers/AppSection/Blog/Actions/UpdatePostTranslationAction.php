<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Tasks\FindPostTask;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\LanguageAdvanced\Actions\UpdateSlugTranslationAction;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdatePostTranslationAction extends ParentAction
{
    public function __construct(
        private readonly FindPostTask $findPostTask,
        private readonly UpdateSlugTranslationAction $updateSlugTranslationAction,
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
        ?array $seoMeta = null
    ): Post
    {
        $post = $this->findPostTask->run($id);

        LanguageAdvancedManager::saveTranslation($post, $data, $langCode);

        if ($seoMeta !== null) {
            $post->setMeta($this->buildSeoMetaKey($langCode), $seoMeta);
        }

        $slugKey = $this->resolveSlugKey($slug, $data['name'] ?? null);
        $slugId = $post->slugable?->getKey();
        if ($slugKey !== null && $slugId) {
            $this->updateSlugTranslationAction->run((int) $slugId, $langCode, $slugKey, Post::class);
        }

        LanguageAdvancedManager::setTranslationLocale($langCode);

        $post->load(LanguageAdvancedManager::withTranslations(['slugable'], Post::class, $langCode));

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
}
