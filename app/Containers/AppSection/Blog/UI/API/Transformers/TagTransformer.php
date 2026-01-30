<?php

namespace App\Containers\AppSection\Blog\UI\API\Transformers;

use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Transformers\Traits\HasOriginLang;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use League\Fractal\Resource\Collection;

final class TagTransformer extends ParentTransformer
{
    use HasOriginLang;

    protected array $availableIncludes = [
        'translations',
    ];

    public function transform(Tag $tag): array
    {
        return [
            'type' => $tag->getResourceKey(),
            'id' => $tag->getHashedKey(),
            'name' => $tag->name,
            'description' => $tag->description,
            'status' => $tag->status?->value ?? (string) $tag->status,
            'slug' => $tag->slug,
            'url' => $tag->url,
            'seo_meta' => $tag->getMeta('seo_meta'),
            'origin_lang' => $this->getOriginLang(),
            'created_at' => $tag->created_at?->toISOString(),
            'updated_at' => $tag->updated_at?->toISOString(),
        ];
    }

    public function includeTranslations(Tag $tag): Collection
    {
        $langCode = $this->resolveLangCode();
        if ($langCode === null) {
            return $this->collection(collect(), new TagTranslationTransformer($tag));
        }

        $tag->loadMissing([
            'translations' => static fn ($query) => $query->where('lang_code', $langCode),
            'slugable.translations' => static fn ($query) => $query->where('lang_code', $langCode),
        ]);

        $translations = $tag->translations->where('lang_code', $langCode)->values();

        return $this->collection($translations, new TagTranslationTransformer($tag));
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
