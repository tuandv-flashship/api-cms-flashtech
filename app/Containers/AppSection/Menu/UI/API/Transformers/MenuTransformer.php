<?php

namespace App\Containers\AppSection\Menu\UI\API\Transformers;

use App\Containers\AppSection\Menu\Models\Menu;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use League\Fractal\Resource\Collection;

final class MenuTransformer extends ParentTransformer
{
    protected array $defaultIncludes = [
        'locations',
        'nodes',
    ];

    public function transform(Menu $menu): array
    {
        return [
            'type' => $menu->getResourceKey(),
            'id' => $menu->getHashedKey(),
            'name' => $menu->name,
            'slug' => $menu->slug,
            'status' => $menu->status,
            'created_at' => $menu->created_at?->toISOString(),
            'updated_at' => $menu->updated_at?->toISOString(),
        ];
    }

    public function includeLocations(Menu $menu): Collection
    {
        if (! $menu->relationLoaded('locations')) {
            return $this->collection(collect(), new MenuLocationTransformer());
        }

        return $this->collection($menu->locations, new MenuLocationTransformer());
    }

    public function includeNodes(Menu $menu): Collection
    {
        if (! $menu->relationLoaded('nodes')) {
            return $this->collection(collect(), new MenuNodeTransformer());
        }

        return $this->collection($menu->nodes, new MenuNodeTransformer());
    }
}
