<?php

namespace App\Containers\AppSection\Gallery\UI\API\Transformers;

use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\Gallery\Models\GalleryTranslation;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class GalleryTranslationTransformer extends ParentTransformer
{
    public function __construct(private readonly Gallery $gallery)
    {
    }

    public function transform(GalleryTranslation $translation): array
    {
        $langCode = (string) $translation->lang_code;

        return [
            'type' => $translation->getResourceKey(),
            'lang_code' => $langCode,
            'name' => $translation->name,
            'description' => $translation->description,
            'slug' => $this->resolveSlug($langCode),
            'seo_meta' => $this->gallery->getMeta($langCode . '_seo_meta'),
            'gallery' => $this->resolveGalleryImages($langCode),
        ];
    }

    private function resolveSlug(string $langCode): ?string
    {
        $slug = $this->gallery->slugable;
        if (! $slug || ! $slug->relationLoaded('translations')) {
            return null;
        }

        $translation = $slug->translations->firstWhere('lang_code', $langCode);

        return $translation?->key;
    }

    private function resolveGalleryImages(string $langCode): array|null
    {
        $meta = $this->gallery->meta;
        if (! $meta || ! $meta->relationLoaded('translations')) {
            return null;
        }

        $translation = $meta->translations->firstWhere('lang_code', $langCode);

        return $translation?->images;
    }
}
