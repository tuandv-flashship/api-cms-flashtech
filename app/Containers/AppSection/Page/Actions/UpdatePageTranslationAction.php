<?php

namespace App\Containers\AppSection\Page\Actions;

use App\Containers\AppSection\CustomField\Supports\CustomFieldService;
use App\Containers\AppSection\LanguageAdvanced\Actions\UpdateSlugTranslationAction;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Tasks\FindPageTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdatePageTranslationAction extends ParentAction
{
    public function __construct(
        private readonly FindPageTask $findPageTask,
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
        array|string|null $customFields = null
    ): Page {
        $page = $this->findPageTask->run($id, ['slugable']);

        LanguageAdvancedManager::saveTranslation($page, $data, $langCode);

        if ($seoMeta !== null) {
            $page->setMeta($this->buildSeoMetaKey($langCode), $seoMeta);
        }

        if ($customFields !== null) {
            $this->customFieldService->saveCustomFieldsForModel($page, $customFields, $langCode);
        }

        $slugKey = $this->resolveSlugKey($slug, $data['name'] ?? null);
        $slugId = $page->slugable?->getKey();
        if ($slugKey !== null && $slugId) {
            $this->updateSlugTranslationAction->run((int) $slugId, $langCode, $slugKey, Page::class);
        }

        LanguageAdvancedManager::setTranslationLocale($langCode);

        $page->load(LanguageAdvancedManager::withTranslations(['slugable', 'user'], Page::class, $langCode));
        $page->loadMissing([
            'slugable.translations' => static fn ($query) => $query->where('lang_code', $langCode),
        ]);

        return $page;
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
