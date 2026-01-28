<?php

namespace App\Containers\AppSection\Blog\UI\API\Transformers;

use App\Containers\AppSection\Blog\Models\Category;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use League\Fractal\Resource\Item;

final class CategoryTransformer extends ParentTransformer
{
    protected array $availableIncludes = [
        'parent',
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
            'parent_id' => $this->hashId($category->parent_id),
            'icon' => $category->icon,
            'order' => $category->order,
            'is_featured' => (bool) $category->is_featured,
            'is_default' => (bool) $category->is_default,
            'created_at' => $category->created_at?->toISOString(),
            'updated_at' => $category->updated_at?->toISOString(),
        ];
    }

    public function includeParent(Category $category): Item
    {
        return $this->item($category->parent, new self());
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
