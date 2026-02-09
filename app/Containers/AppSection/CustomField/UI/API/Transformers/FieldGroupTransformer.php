<?php

namespace App\Containers\AppSection\CustomField\UI\API\Transformers;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Ship\Parents\Transformers\Transformer as ParentTransformer;
use Illuminate\Support\Collection;
use League\Fractal\Resource\Collection as FractalCollection;

final class FieldGroupTransformer extends ParentTransformer
{
    protected array $availableIncludes = [
        'items',
    ];

    public function transform(FieldGroup $group): array
    {
        return [
            'type' => $group->getResourceKey(),
            'id' => $group->getHashedKey(),
            'title' => $group->title,
            'rules' => $this->decodeRules($group->rules),
            'order' => $group->order,
            'status' => $group->status?->value ?? (string) $group->status,
            'created_by' => $this->hashId($group->created_by),
            'updated_by' => $this->hashId($group->updated_by),
            'created_at' => $group->created_at?->toISOString(),
            'updated_at' => $group->updated_at?->toISOString(),
        ];
    }

    public function includeItems(FieldGroup $group): FractalCollection
    {
        $items = $this->buildItems($group);

        return $this->collection($items, new FieldItemTransformer());
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildItems(FieldGroup $group): array
    {
        $items = FieldItem::query()
            ->where('field_group_id', $group->getKey())
            ->orderBy('order')
            ->get();

        if ($items->isEmpty()) {
            return [];
        }

        $byParent = $items->groupBy('parent_id');

        return $this->buildTree($byParent, null);
    }

    /**
     * @param Collection<int, FieldItem> $byParent
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(Collection $byParent, ?int $parentId): array
    {
        $nodes = [];
        $children = $byParent->get($parentId, collect());

        foreach ($children as $item) {
            $nodes[] = $this->formatItem(
                $item,
                $this->buildTree($byParent, (int) $item->getKey())
            );
        }

        return $nodes;
    }

    /**
     * @param array<int, array<string, mixed>> $children
     * @return array<string, mixed>
     */
    private function formatItem(FieldItem $item, array $children): array
    {
        return [
            'id' => $this->hashId($item->getKey()),
            'field_group_id' => $this->hashId($item->field_group_id),
            'parent_id' => $this->hashId($item->parent_id),
            'title' => $item->title,
            'slug' => $item->slug,
            'instructions' => $item->instructions,
            'type' => $item->type,
            'options' => $this->decodeOptions($item->options),
            'order' => $item->order,
            'items' => $children,
        ];
    }

    private function decodeRules(?string $rules): array
    {
        if ($rules === null || trim($rules) === '') {
            return [];
        }

        $decoded = json_decode($rules, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function decodeOptions(?string $options): mixed
    {
        if ($options === null || trim($options) === '') {
            return null;
        }

        $decoded = json_decode($options, true);

        return is_array($decoded) ? $decoded : null;
    }


}
