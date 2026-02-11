<?php

namespace App\Containers\AppSection\Page\Supports;

use App\Containers\AppSection\Blog\Enums\ContentStatus;

final class PageOptions
{

    public static function pageOptions(): array
    {
        return [
            'statuses' => self::statusOptions(),
            'templates' => self::templateOptions(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function templateKeys(): array
    {
        $templates = (array) config('page.templates', []);

        $keys = [];
        foreach ($templates as $key => $value) {
            $keys[] = is_int($key) ? (string) $value : (string) $key;
        }

        $keys = array_filter(array_unique($keys));

        return array_values($keys);
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    public static function templateOptions(): array
    {
        $options = [];

        foreach (self::templateKeys() as $template) {
            $label = __('page.options.templates.' . $template);
            if ($label === 'page.options.templates.' . $template) {
                $label = $template;
            }

            $options[] = [
                'value' => $template,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private static function statusOptions(): array
    {
        return [
            ['value' => ContentStatus::PUBLISHED->value, 'label' => __('page.options.statuses.published')],
            ['value' => ContentStatus::DRAFT->value, 'label' => __('page.options.statuses.draft')],
            ['value' => ContentStatus::PENDING->value, 'label' => __('page.options.statuses.pending')],
        ];
    }
}
