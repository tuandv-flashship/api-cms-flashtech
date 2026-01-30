<?php

namespace App\Containers\AppSection\CustomField\Supports;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Containers\AppSection\CustomField\Supports\CustomFieldRules;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class FieldGroupManager
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): FieldGroup
    {
        [$payload, $items, $deletedItems] = $this->normalizePayload($data);

        $group = FieldGroup::query()->create($payload);

        $this->syncItems($group, $items, $deletedItems);

        return $group->refresh();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): FieldGroup
    {
        [$payload, $items, $deletedItems] = $this->normalizePayload($data);

        $group = FieldGroup::query()->findOrFail($id);
        $group->fill($payload);
        $group->save();

        $this->syncItems($group, $items, $deletedItems);

        return $group->refresh();
    }

    public function delete(int $id): bool
    {
        return (bool) FieldGroup::query()->whereKey($id)->delete();
    }

    /**
     * @param array<string, mixed> $data
     * @return array{0: array<string, mixed>, 1: array<int, array<string, mixed>>, 2: array<int, int>}
     */
    private function normalizePayload(array $data): array
    {
        $payload = Arr::only($data, ['title', 'order', 'status', 'rules', 'created_by', 'updated_by']);

        if (! array_key_exists('created_by', $payload)) {
            $payload['created_by'] = auth()->id();
        }

        $payload['updated_by'] = auth()->id();

        if (isset($payload['rules']) && is_array($payload['rules'])) {
            $payload['rules'] = json_encode($this->normalizeRuleGroups($payload['rules']));
        } elseif (array_key_exists('rules', $payload) && is_string($payload['rules'])) {
            $decoded = json_decode($payload['rules'], true);
            if (is_array($decoded)) {
                $payload['rules'] = json_encode($this->normalizeRuleGroups($decoded));
            }
        }

        $items = $this->normalizeItems($data['group_items'] ?? []);
        $deletedItems = $this->normalizeDeletedItems($data['deleted_items'] ?? []);

        return [$payload, $items, $deletedItems];
    }

    /**
     * @param array<int, mixed>|string $items
     * @return array<int, array<string, mixed>>
     */
    private function normalizeItems(array|string $items): array
    {
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        if (! is_array($items)) {
            return [];
        }

        $normalized = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $normalized[] = $item;
        }

        return $normalized;
    }

    /**
     * @param array<int, mixed>|array<string, mixed> $rules
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRuleGroups(array $rules): array
    {
        if ($rules === []) {
            return [];
        }

        if ($this->isRuleGroup($rules)) {
            $normalized = CustomFieldRules::normalizeRules($rules);

            return $normalized === [] ? [] : [$normalized];
        }

        $groups = [];
        foreach ($rules as $group) {
            if (! is_array($group)) {
                continue;
            }

            $normalized = CustomFieldRules::normalizeRules($group);
            if ($normalized !== []) {
                $groups[] = $normalized;
            }
        }

        return $groups;
    }

    /**
     * @param array<int, mixed>|array<string, mixed> $rules
     */
    private function isRuleGroup(array $rules): bool
    {
        if (array_key_exists('name', $rules)) {
            return true;
        }

        if (array_key_exists(0, $rules) && is_array($rules[0])) {
            return array_key_exists('name', $rules[0]);
        }

        return false;
    }

    /**
     * @param array<int, mixed>|string $items
     * @return array<int, int>
     */
    private function normalizeDeletedItems(array|string $items): array
    {
        if (is_string($items)) {
            $decoded = json_decode($items, true);
            if (is_array($decoded)) {
                $items = $decoded;
            }
        }

        if (! is_array($items)) {
            return [];
        }

        $deleted = [];
        foreach ($items as $item) {
            $decoded = $this->decodeId($item);
            if ($decoded !== null) {
                $deleted[] = $decoded;
            }
        }

        return array_values(array_unique($deleted));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<int, int> $deletedItems
     */
    private function syncItems(FieldGroup $group, array $items, array $deletedItems): void
    {
        if ($deletedItems !== []) {
            FieldItem::query()->whereIn('id', $deletedItems)->delete();
        }

        $this->upsertItems($items, $group->getKey(), null);
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function upsertItems(array $items, int $groupId, ?int $parentId): void
    {
        $position = 0;

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $position++;

            $saved = $this->upsertItem($item, $groupId, $parentId, $position);
            if (! $saved) {
                continue;
            }

            $children = $item['items'] ?? [];
            if (is_array($children) && $children !== []) {
                $this->upsertItems($children, $groupId, $saved->getKey());
            }
        }
    }

    /**
     * @param array<string, mixed> $item
     */
    private function upsertItem(array $item, int $groupId, ?int $parentId, int $position): ?FieldItem
    {
        $id = $this->decodeId($item['id'] ?? null);

        $title = trim((string) ($item['title'] ?? ''));
        $type = trim((string) ($item['type'] ?? ''));

        if ($title === '' || $type === '') {
            return null;
        }

        $slug = trim((string) ($item['slug'] ?? ''));
        $slug = $slug !== '' ? $slug : $title;
        $slug = Str::slug($slug, '_');
        if ($slug === '') {
            $slug = 'field_' . $position;
        }

        $slug = $this->ensureUniqueSlug($groupId, $parentId, $id, $slug, $position);

        $options = $item['options'] ?? null;
        if (is_array($options) || is_object($options)) {
            $options = json_encode($options);
        }

        $payload = [
            'field_group_id' => $groupId,
            'parent_id' => $parentId,
            'title' => $title,
            'order' => $position,
            'type' => $type,
            'options' => $options,
            'instructions' => isset($item['instructions']) ? (string) $item['instructions'] : null,
            'slug' => $slug,
        ];

        if ($id !== null) {
            $existing = FieldItem::query()->find($id);
            if ($existing) {
                $existing->fill($payload);
                $existing->save();

                return $existing;
            }
        }

        return FieldItem::query()->create($payload);
    }

    private function ensureUniqueSlug(int $groupId, ?int $parentId, ?int $id, string $slug, int $position): string
    {
        $existing = FieldItem::query()
            ->where('field_group_id', $groupId)
            ->where('parent_id', $parentId)
            ->where('slug', $slug)
            ->first();

        if ($existing && $existing->getKey() !== $id) {
            return $slug . '_' . time() . $position;
        }

        return $slug;
    }

    private function decodeId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value;
        }

        $numeric = filter_var($value, FILTER_VALIDATE_INT);
        if ($numeric !== false && ! config('apiato.hash-id')) {
            return (int) $numeric;
        }

        if (! is_string($value) || ! config('apiato.hash-id')) {
            return is_numeric($value) ? (int) $value : null;
        }

        try {
            $decoded = hashids()->decodeOrFail($value);
        } catch (InvalidArgumentException) {
            return null;
        }

        return is_int($decoded) ? $decoded : (int) Arr::first((array) $decoded, null, 0);
    }
}
