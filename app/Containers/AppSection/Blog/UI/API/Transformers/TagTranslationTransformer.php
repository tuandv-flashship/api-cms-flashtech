<?php

namespace App\Containers\AppSection\Blog\UI\API\Transformers;

use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Models\TagTranslation;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class TagTranslationTransformer extends ParentTransformer
{
    public function __construct(private readonly Tag $tag)
    {
    }

    public function transform(TagTranslation $translation): array
    {
        $langCode = (string) $translation->lang_code;

        return [
            'type' => $translation->getResourceKey(),
            'lang_code' => $langCode,
            'name' => $translation->name,
            'description' => $translation->description,
            'slug' => $this->resolveSlug($langCode),
            'seo_meta' => $this->tag->getMeta($langCode . '_seo_meta'),
        ];
    }

    private function resolveSlug(string $langCode): ?string
    {
        $slug = $this->tag->slugable;
        if (! $slug || ! $slug->relationLoaded('translations')) {
            return null;
        }

        $translation = $slug->translations->firstWhere('lang_code', $langCode);

        return $translation?->key;
    }
}
