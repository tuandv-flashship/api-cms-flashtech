<?php

namespace App\Containers\AppSection\Blog\UI\API\Transformers;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\PostTranslation;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class PostTranslationTransformer extends ParentTransformer
{
    public function __construct(private readonly Post $post)
    {
    }

    public function transform(PostTranslation $translation): array
    {
        $langCode = (string) $translation->lang_code;

        return [
            'type' => $translation->getResourceKey(),
            'lang_code' => $langCode,
            'name' => $translation->name,
            'description' => $translation->description,
            'content' => $translation->content,
            'slug' => $this->resolveSlug($langCode),
            'seo_meta' => $this->post->getMeta($langCode . '_seo_meta'),
        ];
    }

    private function resolveSlug(string $langCode): ?string
    {
        $slug = $this->post->slugable;
        if (! $slug || ! $slug->relationLoaded('translations')) {
            return null;
        }

        $translation = $slug->translations->firstWhere('lang_code', $langCode);

        return $translation?->key;
    }
}
