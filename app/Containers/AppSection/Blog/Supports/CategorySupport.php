<?php

namespace App\Containers\AppSection\Blog\Supports;

use App\Containers\AppSection\Blog\Models\Category;

final class CategorySupport
{
    /**
     * Reset other default categories if the current one is set as default.
     *
     * @param array<string, mixed> $data
     */
    public static function resetDefaultCategoryIfRequested(array $data): void
    {
        if (! empty($data['is_default'])) {
            Category::query()->where('is_default', true)->update(['is_default' => false]);
        }
    }
}
