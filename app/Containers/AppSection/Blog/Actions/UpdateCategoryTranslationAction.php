<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Tasks\FindCategoryTask;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\LanguageAdvanced\Actions\UpdateSlugTranslationAction;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateCategoryTranslationAction extends ParentAction
{
    public function __construct(
        private readonly FindCategoryTask $findCategoryTask,
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
    ): Category
    {
        $category = $this->findCategoryTask->run($id);

        LanguageAdvancedManager::saveTranslation($category, $data, $langCode);

        if ($seoMeta !== null) {
            $category->setMeta($this->buildSeoMetaKey($langCode), $seoMeta);
        }

        $slugKey = $this->resolveSlugKey($slug, $data['name'] ?? null);
        $slugId = $category->slugable?->getKey();
        if ($slugKey !== null && $slugId) {
            $this->updateSlugTranslationAction->run((int) $slugId, $langCode, $slugKey, Category::class);
        }

        LanguageAdvancedManager::setTranslationLocale($langCode);

        $category->load(LanguageAdvancedManager::withTranslations(['slugable'], Category::class, $langCode));

        return $category;
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
