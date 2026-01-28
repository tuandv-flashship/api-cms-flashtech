<?php

namespace App\Containers\AppSection\Gallery\Supports;

use App\Containers\AppSection\Blog\Enums\ContentStatus;

final class GalleryOptions
{
    public static function shouldIncludeOptions(?string $include): bool
    {
        if ($include === null) {
            return false;
        }

        $include = trim($include);
        if ($include === '') {
            return false;
        }

        $includes = array_map('trim', explode(',', $include));

        return in_array('options', $includes, true);
    }

    public static function galleryOptions(): array
    {
        return [
            'statuses' => self::statusOptions(),
            'boolean' => self::booleanOptions(),
        ];
    }

    private static function statusOptions(): array
    {
        return [
            ['value' => ContentStatus::PUBLISHED->value, 'label' => __('gallery.options.statuses.published')],
            ['value' => ContentStatus::DRAFT->value, 'label' => __('gallery.options.statuses.draft')],
            ['value' => ContentStatus::PENDING->value, 'label' => __('gallery.options.statuses.pending')],
        ];
    }

    private static function booleanOptions(): array
    {
        return [
            ['value' => 1, 'label' => __('settings.options.boolean.on')],
            ['value' => 0, 'label' => __('settings.options.boolean.off')],
        ];
    }
}
