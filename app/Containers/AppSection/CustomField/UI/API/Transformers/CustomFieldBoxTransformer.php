<?php

namespace App\Containers\AppSection\CustomField\UI\API\Transformers;

use App\Ship\Parents\Transformers\Transformer as ParentTransformer;

final class CustomFieldBoxTransformer extends ParentTransformer
{
    /**
     * @param array<string, mixed> $group
     */
    public function transform(array $group): array
    {
        $items = $group['items'] ?? [];
        if (! is_array($items)) {
            $items = [];
        }

        return [
            'type' => 'CustomFieldBox',
            'id' => $this->hashId($group['id'] ?? null),
            'title' => $group['title'] ?? null,
            'items' => $this->normalizeItems($items),
        ];
    }

    /**
     * @param array<int, mixed> $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $normalized[] = $this->normalizeItem($item);
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    private function normalizeItem(array $item): array
    {
        $children = $item['items'] ?? [];
        if (! is_array($children)) {
            $children = [];
        }

        $normalized = [
            'id' => $this->hashId($item['id'] ?? null),
            'title' => $item['title'] ?? null,
            'slug' => $item['slug'] ?? null,
            'instructions' => $item['instructions'] ?? null,
            'type' => $item['type'] ?? null,
            'options' => $item['options'] ?? null,
            'items' => $this->normalizeItems($children),
        ];

        if (array_key_exists('value', $item)) {
            $normalized['value'] = $this->normalizeValue($item['type'] ?? null, $item['value'] ?? null);
        }

        if (array_key_exists('thumb', $item)) {
            $normalized['thumb'] = $item['thumb'];
        }

        if (array_key_exists('full_url', $item)) {
            $normalized['full_url'] = $item['full_url'];
        }

        return $normalized;
    }

    private function normalizeValue(?string $type, mixed $value): mixed
    {
        if ($type !== 'repeater' || ! is_array($value)) {
            return $value;
        }

        $normalized = [];
        foreach ($value as $rowIndex => $row) {
            if (! is_array($row)) {
                continue;
            }

            $rowItems = [];
            foreach ($row as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $rowItems[] = $this->normalizeItem($item);
            }

            $normalized[$rowIndex] = $rowItems;
        }

        return $normalized;
    }


}
