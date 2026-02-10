<?php

namespace App\Containers\AppSection\Gallery\UI\API\Transformers;

use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Media\Supports\MediaRuntimeServices;
use App\Ship\Parents\Transformers\Traits\HasOriginLang;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use League\Fractal\Resource\Collection;

final class GalleryTransformer extends ParentTransformer
{
    use HasOriginLang;

    protected array $availableIncludes = [
        'translations',
    ];

    public function transform(Gallery $gallery): array
    {
        $meta = $gallery->relationLoaded('meta') ? $gallery->meta : null;
        $mediaService = MediaRuntimeServices::mediaService();

        return [
            'type' => $gallery->getResourceKey(),
            'id' => $gallery->getHashedKey(),
            'name' => $gallery->name,
            'description' => $gallery->description,
            'status' => $gallery->status?->value ?? (string) $gallery->status,
            'is_featured' => (bool) $gallery->is_featured,
            'order' => $gallery->order,
            'image' => $mediaService->getImageUrl($gallery->image),
            'slug' => $gallery->slug,
            'url' => $gallery->url,
            'seo_meta' => $gallery->getMeta('seo_meta'),
            'author_id' => $this->hashId($gallery->author_id),
            'author_type' => $gallery->author_type,
            'gallery' => $meta ? ($meta->images ?? []) : [],
            'origin_lang' => $this->getOriginLang(),
            'created_at' => $gallery->created_at?->toISOString(),
            'updated_at' => $gallery->updated_at?->toISOString(),
        ];
    }

    public function includeTranslations(Gallery $gallery): Collection
    {
        $langCode = $this->resolveLangCode();
        if ($langCode === null) {
            return $this->collection(collect(), new GalleryTranslationTransformer($gallery));
        }

        $gallery->loadMissing([
            'translations' => static fn ($query) => $query->where('lang_code', $langCode),
            'slugable.translations' => static fn ($query) => $query->where('lang_code', $langCode),
            'meta.translations' => static fn ($query) => $query->where('lang_code', $langCode),
        ]);

        $translations = $gallery->translations->where('lang_code', $langCode)->values();

        return $this->collection($translations, new GalleryTranslationTransformer($gallery));
    }

    private function resolveLangCode(): ?string
    {
        $langCode = request()->query('lang_code') ?? request()->query('language');
        if (! $langCode) {
            return null;
        }

        $normalized = LanguageAdvancedManager::normalizeLanguageCode((string) $langCode);

        return $normalized ?? (string) $langCode;
    }


}
