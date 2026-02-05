<?php

namespace App\Containers\AppSection\Blog\UI\API\Transformers;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Transformers\Traits\HasOriginLang;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use League\Fractal\Resource\Primitive;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

final class CategoryTransformer extends ParentTransformer
{
    use HasOriginLang;

    protected array $availableIncludes = [
        'parent',
        'children',
        'translations',
    ];

    public function transform(Category $category): array
    {
        return [
            'type' => $category->getResourceKey(),
            'id' => $category->getHashedKey(),
            'name' => $category->name,
            'description' => $category->description,
            'status' => $category->status?->value ?? (string) $category->status,
            'slug' => $category->slug,
            'url' => $category->url,
            'seo_meta' => $category->getMeta('seo_meta'),
            'parent_id' => $this->hashId($category->parent_id),
            'icon' => $category->icon,
            'order' => $category->order,
            'is_featured' => (bool) $category->is_featured,
            'is_default' => (bool) $category->is_default,
            'origin_lang' => $this->getOriginLang(),
            'created_at' => $category->created_at?->toISOString(),
            'updated_at' => $category->updated_at?->toISOString(),
        ];
    }

    public function includeParent(Category $category): Primitive|Item
    {
        if (! $category->parent_id || (int) $category->parent_id <= 0) {
            return $this->nullableItem(null, new self());
        }

        return $this->nullableItem($category->parent, new self());
    }

    public function includeChildren(Category $category): Collection
    {
        $category->loadMissing('children'); // Fix Lazy Loading
        return $this->collection($category->children, new self());
    }

    public function includeTranslations(Category $category): Collection
    {
        $langCode = $this->resolveLangCode();
        if ($langCode === null) {
            return $this->collection(collect(), new CategoryTranslationTransformer($category));
        }

        $category->loadMissing([
            'translations' => static fn ($query) => $query->where('lang_code', $langCode),
            'slugable.translations' => static fn ($query) => $query->where('lang_code', $langCode),
        ]);

        $translations = $category->translations->where('lang_code', $langCode)->values();

        return $this->collection($translations, new CategoryTranslationTransformer($category));
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

    private function hashId(int|string|null $id): int|string|null
    {
        if ($id === null) {
            return null;
        }

        $intId = (int) $id;
        if ($intId <= 0) {
            return $intId;
        }

        return config('apiato.hash-id') ? hashids()->encodeOrFail($intId) : $intId;
    }
}
