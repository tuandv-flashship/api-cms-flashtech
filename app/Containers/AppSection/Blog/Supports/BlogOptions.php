<?php

namespace App\Containers\AppSection\Blog\Supports;

use App\Containers\AppSection\Blog\Enums\ContentStatus;

final class BlogOptions
{

    public static function postOptions(): array
    {
        return [
            'statuses' => self::statusOptions(),
            'boolean' => self::booleanOptions(),
            'format_types' => self::formatTypeOptions(),
        ];
    }

    public static function categoryOptions(): array
    {
        return [
            'statuses' => self::statusOptions(),
            'boolean' => self::booleanOptions(),
        ];
    }

    public static function tagOptions(): array
    {
        return [
            'statuses' => self::statusOptions(),
        ];
    }

    private static function statusOptions(): array
    {
        return [
            ['value' => ContentStatus::PUBLISHED->value, 'label' => __('blog.options.statuses.published')],
            ['value' => ContentStatus::DRAFT->value, 'label' => __('blog.options.statuses.draft')],
            ['value' => ContentStatus::PENDING->value, 'label' => __('blog.options.statuses.pending')],
        ];
    }

    private static function booleanOptions(): array
    {
        return [
            ['value' => 1, 'label' => __('settings.options.boolean.on')],
            ['value' => 0, 'label' => __('settings.options.boolean.off')],
        ];
    }

    private static function formatTypeOptions(): array
    {
        $options = [];

        foreach (PostFormat::toPairs() as $item) {
            $options[] = [
                'value' => $item[0],
                'label' => $item[1],
            ];
        }

        return $options;
    }
}
