<?php

namespace App\Containers\AppSection\CustomField\Supports;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\CustomField\Supports\CustomFieldRules;
use Illuminate\Support\Str;

final class CustomFieldOptions
{

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function options(): array
    {
        return [
            'statuses' => self::statusOptions(),
            'field_types' => self::fieldTypeOptions(),
            'rule_groups' => self::ruleGroupOptions(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function supportedModules(): array
    {
        return (array) config('custom-field.supported', []);
    }

    public static function resolveModelClass(string $model): ?string
    {
        $supported = self::supportedModules();

        if (isset($supported[$model])) {
            return (string) $supported[$model];
        }

        if (in_array($model, $supported, true)) {
            return $model;
        }

        return null;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private static function statusOptions(): array
    {
        return [
            ['value' => ContentStatus::PUBLISHED->value, 'label' => __('custom-field.options.statuses.published')],
            ['value' => ContentStatus::DRAFT->value, 'label' => __('custom-field.options.statuses.draft')],
            ['value' => ContentStatus::PENDING->value, 'label' => __('custom-field.options.statuses.pending')],
        ];
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private static function fieldTypeOptions(): array
    {
        $types = [
            'text',
            'number',
            'email',
            'password',
            'url',
            'date',
            'datetime',
            'time',
            'color',
            'textarea',
            'checkbox',
            'radio',
            'select',
            'image',
            'file',
            'wysiwyg',
            'repeater',
        ];

        $options = [];
        foreach ($types as $type) {
            $labelKey = 'custom-field.options.field_types.' . $type;
            $label = __($labelKey);
            if ($label === $labelKey) {
                $label = Str::title(str_replace(['-', '_'], ' ', $type));
            }

            $options[] = [
                'value' => $type,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function ruleGroupOptions(): array
    {
        $groups = [];
        foreach (CustomFieldRules::groups() as $groupKey => $items) {
            $labelKey = 'custom-field.options.rule_groups.' . $groupKey;
            $label = __($labelKey);
            if ($label === $labelKey) {
                $label = Str::title(str_replace(['-', '_'], ' ', $groupKey));
            }

            $group = [
                'key' => $groupKey,
                'label' => $label,
                'items' => [],
            ];

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $slug = (string) ($item['slug'] ?? '');
                if ($slug === '') {
                    continue;
                }

                $ruleKey = CustomFieldRules::ruleKey($slug);
                $groupItem = [
                    'value' => $ruleKey,
                    'label' => $item['label'] ?? $ruleKey,
                ];

                if (array_key_exists('data', $item)) {
                    $groupItem['data'] = $item['data'];
                }

                if (array_key_exists('source', $item)) {
                    $groupItem['source'] = $item['source'];
                }

                $group['items'][] = $groupItem;
            }

            $groups[] = $group;
        }

        return $groups;
    }
}
