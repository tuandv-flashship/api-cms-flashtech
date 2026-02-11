<?php

namespace App\Containers\AppSection\Menu\UI\API\Transformers;

use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use League\Fractal\Resource\Collection;

final class MenuNodeTransformer extends ParentTransformer
{
    protected array $defaultIncludes = [
        'children',
    ];

    public function transform(MenuNode $node): array
    {
        return [
            'type' => $node->getResourceKey(),
            'id' => $node->getHashedKey(),
            'menu_id' => $this->hashId((int) $node->menu_id),
            'parent_id' => $node->parent_id ? $this->hashId((int) $node->parent_id) : null,
            'reference_type' => $node->reference_type,
            'reference_id' => $node->reference_id ? $this->hashId((int) $node->reference_id) : null,
            'url' => $node->url,
            'title' => $node->title,
            'url_source' => $node->url_source,
            'title_source' => $node->title_source,
            'icon_font' => $node->icon_font,
            'css_class' => $node->css_class,
            'target' => $node->target,
            'has_child' => (bool) $node->has_child,
            'position' => (int) $node->position,
            'created_at' => $node->created_at?->toISOString(),
            'updated_at' => $node->updated_at?->toISOString(),
        ];
    }

    public function includeChildren(MenuNode $node): Collection
    {
        if (! $node->relationLoaded('children')) {
            return $this->collection(collect(), new self());
        }

        return $this->collection($node->children, new self());
    }
}
