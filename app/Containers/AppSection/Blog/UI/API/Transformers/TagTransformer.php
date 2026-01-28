<?php

namespace App\Containers\AppSection\Blog\UI\API\Transformers;

use App\Containers\AppSection\Blog\Models\Tag;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class TagTransformer extends ParentTransformer
{
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
            'created_at' => $tag->created_at?->toISOString(),
            'updated_at' => $tag->updated_at?->toISOString(),
        ];
    }
}
