<?php

namespace App\Containers\AppSection\Page\UI\API\Transformers;

use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Models\PageTranslation;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class PageTranslationTransformer extends ParentTransformer
{
    public function __construct(private readonly Page $page)
    {
    }

    public function transform(PageTranslation $translation): array
    {
        $langCode = (string) $translation->lang_code;

        return [
            'type' => $translation->getResourceKey(),
            'lang_code' => $langCode,
            'name' => $translation->name,
            'description' => $translation->description,
            'content' => $translation->content,
            'slug' => $this->resolveSlug($langCode),
            'seo_meta' => $this->page->getMeta($langCode . '_seo_meta'),
        ];
    }

    private function resolveSlug(string $langCode): ?string
    {
        $slug = $this->page->slugable;
        if (! $slug || ! $slug->relationLoaded('translations')) {
            return null;
        }

        $translation = $slug->translations->firstWhere('lang_code', $langCode);

        return $translation?->key;
    }
}
