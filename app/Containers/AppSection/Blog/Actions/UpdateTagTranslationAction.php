<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Tasks\FindTagTask;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\LanguageAdvanced\Actions\UpdateSlugTranslationAction;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateTagTranslationAction extends ParentAction
{
    public function __construct(
        private readonly FindTagTask $findTagTask,
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
    ): Tag
    {
        $tag = $this->findTagTask->run($id);

        LanguageAdvancedManager::saveTranslation($tag, $data, $langCode);

        if ($seoMeta !== null) {
            $tag->setMeta($this->buildSeoMetaKey($langCode), $seoMeta);
        }

        $slugKey = $this->resolveSlugKey($slug, $data['name'] ?? null);
        $slugId = $tag->slugable?->getKey();
        if ($slugKey !== null && $slugId) {
            $this->updateSlugTranslationAction->run((int) $slugId, $langCode, $slugKey, Tag::class);
        }

        LanguageAdvancedManager::setTranslationLocale($langCode);

        $tag->load(LanguageAdvancedManager::withTranslations(['slugable'], Tag::class, $langCode));

        return $tag;
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
