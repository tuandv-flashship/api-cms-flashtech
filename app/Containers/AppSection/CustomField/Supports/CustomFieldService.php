<?php

namespace App\Containers\AppSection\CustomField\Supports;

use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Containers\AppSection\CustomField\Models\CustomFieldTranslation;
use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Containers\AppSection\CustomField\Models\FieldItem;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Media\Services\MediaService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use InvalidArgumentException;

final class CustomFieldService
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    /**
     * @param array<int, mixed>|string|null $customFields
     */
    public function saveCustomFieldsForModel(Model $model, array|string|null $customFields, ?string $langCode = null): bool
    {
        $rows = $this->parseCustomFields($customFields);
        if ($rows === []) {
            return false;
        }

        $referenceType = $model::class;
        $referenceId = (int) $model->getKey();
        $langCode = $langCode ?? LanguageAdvancedManager::getTranslationLocale();

        foreach ($rows as $row) {
            $this->saveCustomField($referenceType, $referenceId, $row, $langCode);
        }

        return true;
    }

    /**
     * @param array<string, mixed> $rules
     * @return array<int, array<string, mixed>>
     */
    public function exportCustomFieldsData(
        string $referenceType,
        ?int $referenceId = null,
        array $rules = [],
        ?string $langCode = null
    ): array {
        $model = null;
        if ($referenceId !== null && class_exists($referenceType)) {
            $model = $referenceType::query()->find($referenceId);
        }

        $context = CustomFieldRules::buildContext($model, $referenceType, $rules);

        $groups = FieldGroup::query()
            ->published()
            ->orderBy('order')
            ->get();

        $result = [];
        foreach ($groups as $group) {
            $groupRules = $this->decodeRules($group->rules);
            if (! $this->checkRules($groupRules, $context)) {
                continue;
            }

            $result[] = [
                'id' => $group->getKey(),
                'title' => $group->title,
                'items' => $this->getFieldGroupItems(
                    (int) $group->getKey(),
                    null,
                    $referenceType,
                    $referenceId,
                    $langCode
                ),
            ];
        }

        return $result;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getFieldGroupItems(
        int $groupId,
        ?int $parentId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $langCode = null
    ): array {
        $items = FieldItem::query()
            ->where('field_group_id', $groupId)
            ->where('parent_id', $parentId)
            ->orderBy('order')
            ->get();

        $result = [];
        foreach ($items as $item) {
            $entry = [
                'id' => $item->getKey(),
                'title' => $item->title,
                'slug' => $item->slug,
                'instructions' => $item->instructions,
                'type' => $item->type,
                'options' => $this->decodeOptions($item->options),
                'items' => $this->getFieldGroupItems(
                    (int) $groupId,
                    (int) $item->getKey(),
                    $referenceType,
                    $referenceId,
                    $langCode
                ),
            ];

            if ($referenceType !== null && $referenceId !== null) {
                if ($item->type === 'repeater') {
                    $entry['value'] = $this->getRepeaterValue(
                        $entry['items'],
                        $this->getFieldItemValue($item, $referenceType, $referenceId, $langCode)
                    );

                    $this->applyMediaToRepeaterItems($entry['value']);
                } else {
                    $entry['value'] = $this->getFieldItemValue($item, $referenceType, $referenceId, $langCode);
                }

                if ($item->type === 'image' && ! empty($entry['value'])) {
                    $entry['thumb'] = $this->mediaService->getImageUrl((string) $entry['value'], 'thumb');
                }

                if ($item->type === 'file' && ! empty($entry['value'])) {
                    $entry['full_url'] = $this->mediaService->url((string) $entry['value']);
                }
            }

            $result[] = $entry;
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $rules
     */
    private function checkRules(array $rules, array $context): bool
    {
        if ($rules === []) {
            return false;
        }

        foreach ($rules as $ruleGroup) {
            if ($this->checkRuleGroup($ruleGroup, $context)) {
                return true;
            }
        }

        return false;
    }

    private function checkRuleGroup(array $ruleGroup, array $context): bool
    {
        foreach ($ruleGroup as $rule) {
            $name = Arr::get($rule, 'name');
            if (! is_string($name) || ! array_key_exists($name, $context)) {
                return false;
            }

            $value = Arr::get($rule, 'value');
            $type = Arr::get($rule, 'type', '==');

            $current = $context[$name];

            if ($type === '==') {
                $matched = is_array($current)
                    ? in_array($value, $current)
                    : $current == $value;
            } else {
                $matched = is_array($current)
                    ? ! in_array($value, $current)
                    : $current != $value;
            }

            if (! $matched) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, mixed>|string|null $customFields
     * @return array<int, array<string, mixed>>
     */
    private function parseCustomFields(array|string|null $customFields): array
    {
        if ($customFields === null) {
            return [];
        }

        if (is_string($customFields)) {
            $decoded = json_decode($customFields, true);
            if (! is_array($decoded)) {
                return [];
            }
            $customFields = $decoded;
        }

        if (! is_array($customFields)) {
            return [];
        }

        $items = [];
        foreach ($customFields as $group) {
            if (! is_array($group)) {
                continue;
            }

            if (isset($group['items']) && is_array($group['items'])) {
                foreach ($group['items'] as $item) {
                    if (! is_array($item)) {
                        continue;
                    }

                    $this->decodeItemIds($item);
                    $items[] = $item;
                }

                continue;
            }

            if (isset($group['type'])) {
                $this->decodeItemIds($group);
                $items[] = $group;
            }
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function decodeItemIds(array &$item): void
    {
        if (array_key_exists('id', $item)) {
            $item['id'] = $this->decodeId($item['id']) ?? $item['id'];
        }

        if (isset($item['items']) && is_array($item['items'])) {
            foreach ($item['items'] as &$child) {
                if (is_array($child)) {
                    $this->decodeItemIds($child);
                }
            }
        }

        if (isset($item['value']) && is_array($item['value'])) {
            foreach ($item['value'] as &$row) {
                if (! is_array($row)) {
                    continue;
                }

                foreach ($row as &$child) {
                    if (is_array($child)) {
                        $this->decodeItemIds($child);
                    }
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function saveCustomField(string $reference, int $id, array $data, ?string $langCode): void
    {
        $current = CustomField::query()
            ->where([
                'field_item_id' => $data['id'] ?? null,
                'slug' => $data['slug'] ?? null,
                'use_for' => $reference,
                'use_for_id' => $id,
            ])
            ->first();

        $value = $this->parseFieldValue($data);
        if (! is_string($value)) {
            $value = json_encode($value);
        }

        $payload = [
            'value' => $value,
            'type' => $data['type'] ?? 'text',
            'slug' => $data['slug'] ?? null,
        ];

        $langCode = $langCode ?: LanguageAdvancedManager::getTranslationLocale();
        $isDefault = ! $langCode || LanguageAdvancedManager::isDefaultLocale($langCode);

        if (! $isDefault) {
            if (! $current) {
                $payload['use_for'] = $reference;
                $payload['use_for_id'] = $id;
                $payload['field_item_id'] = $data['id'] ?? null;

                $current = CustomField::query()->create($payload);
            }

            if ($current) {
                LanguageAdvancedManager::saveTranslation($current, ['value' => $value], $langCode);
            }

            return;
        }

        if ($current) {
            $current->fill($payload);
            $current->save();

            return;
        }

        $payload['use_for'] = $reference;
        $payload['use_for_id'] = $id;
        $payload['field_item_id'] = $data['id'] ?? null;

        CustomField::query()->create($payload);
    }

    private function parseFieldValue(array $field): array|string
    {
        $value = [];

        switch ($field['type'] ?? '') {
            case 'repeater':
                if (! isset($field['value']) || ! is_array($field['value'])) {
                    break;
                }

                foreach ($field['value'] as $row) {
                    if (! is_array($row)) {
                        continue;
                    }

                    $groups = [];
                    foreach ($row as $item) {
                        if (! is_array($item)) {
                            continue;
                        }

                        $groups[] = [
                            'field_item_id' => $item['id'] ?? null,
                            'type' => $item['type'] ?? null,
                            'slug' => $item['slug'] ?? null,
                            'value' => $this->parseFieldValue($item),
                        ];
                    }

                    $value[] = $groups;
                }

                break;
            case 'checkbox':
                $value = isset($field['value']) ? (array) $field['value'] : [];

                break;
            default:
                $value = $field['value'] ?? '';

                break;
        }

        return $value;
    }

    private function decodeOptions(?string $options): mixed
    {
        if ($options === null || trim($options) === '') {
            return null;
        }

        $decoded = json_decode($options, true);

        return is_array($decoded) ? $decoded : null;
    }

    private function decodeRules(?string $rules): array
    {
        if (! $rules) {
            return [];
        }

        $decoded = json_decode($rules, true);
        if (! is_array($decoded)) {
            return [];
        }

        $normalized = [];
        foreach ($decoded as $group) {
            if (! is_array($group)) {
                continue;
            }

            $normalized[] = CustomFieldRules::normalizeRules($group);
        }

        return $normalized;
    }

    private function getFieldItemValue(FieldItem $fieldItem, string $referenceType, int $referenceId, ?string $langCode): mixed
    {
        $field = CustomField::query()
            ->where([
                'use_for' => $referenceType,
                'use_for_id' => $referenceId,
                'slug' => $fieldItem->slug,
                'field_item_id' => $fieldItem->getKey(),
            ])
            ->first();

        if (! $field) {
            return null;
        }

        $value = $field->value;

        $langCode = $langCode ?: LanguageAdvancedManager::getTranslationLocale();
        if ($langCode && ! LanguageAdvancedManager::isDefaultLocale($langCode)) {
            $translation = CustomFieldTranslation::query()
                ->where('custom_fields_id', $field->getKey())
                ->where('lang_code', $langCode)
                ->value('value');

            if ($translation !== null) {
                $value = $translation;
            }
        }

        if ($fieldItem->type === 'checkbox' && is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        if ($fieldItem->type === 'repeater' && is_string($value)) {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $value;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @param array<int, array<string, mixed>>|string|null $data
     * @return array<int, array<string, mixed>>
     */
    private function getRepeaterValue(array $items, array|string|null $data): array
    {
        if ($items === []) {
            return [];
        }

        if (! is_array($data)) {
            $decoded = json_decode((string) $data, true);
            $data = is_array($decoded) ? $decoded : [];
        }

        if ($data === []) {
            return [];
        }

        $result = [];
        foreach ($data as $rowIndex => $row) {
            if (! is_array($row)) {
                continue;
            }

            $cloned = $items;

            foreach ($cloned as $itemIndex => $item) {
                foreach ($row as $currentData) {
                    if (! is_array($currentData)) {
                        continue;
                    }

                    if ((int) ($item['id'] ?? 0) !== (int) ($currentData['field_item_id'] ?? 0)) {
                        continue;
                    }

                    if (($item['type'] ?? null) === 'repeater') {
                        $item['value'] = $this->getRepeaterValue($item['items'] ?? [], $currentData['value'] ?? []);
                    } else {
                        $item['value'] = $currentData['value'] ?? null;
                    }

                    $cloned[$itemIndex] = $item;
                }
            }

            $result[$rowIndex] = array_values($cloned);
        }

        return $result;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function applyMediaToRepeaterItems(array &$items): void
    {
        foreach ($items as &$row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($row as &$item) {
                if (! is_array($item)) {
                    continue;
                }

                $type = $item['type'] ?? null;
                $value = $item['value'] ?? null;

                if ($type === 'repeater' && is_array($value)) {
                    $this->applyMediaToRepeaterItems($value);
                    $item['value'] = $value;
                }

                if ($type === 'image' && is_string($value) && $value !== '') {
                    $item['thumb'] = $this->mediaService->getImageUrl($value, 'thumb');
                }

                if ($type === 'file' && is_string($value) && $value !== '') {
                    $item['full_url'] = $this->mediaService->url($value);
                }
            }
        }
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
