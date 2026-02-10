<?php

namespace App\Containers\AppSection\Blog\Supports;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Tag;

final class TagResolver
{
    /**
     * Resolve tag IDs from existing IDs and new tag names to create.
     *
     * @param int[]|null $tagIds
     * @param string[]|null $tagNames
     * @return int[]|null
     */
    public static function resolveTagIds(?array $tagIds, ?array $tagNames): ?array
    {
        $tagIds = $tagIds ?? [];
        $tagNames = $tagNames ?? [];

        foreach ($tagNames as $tagName) {
            $tagName = trim((string) $tagName);
            if ($tagName === '') {
                continue;
            }

            $tag = Tag::query()->firstOrCreate(
                ['name' => $tagName],
                ['status' => ContentStatus::PUBLISHED]
            );

            $tagIds[] = $tag->getKey();
        }

        $tagIds = array_values(array_unique(array_filter($tagIds)));

        return $tagIds !== [] ? $tagIds : null;
    }
}
