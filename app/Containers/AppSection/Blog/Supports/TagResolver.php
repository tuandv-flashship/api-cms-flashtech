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
        // Keep information about whether tags were explicitly provided.
        // If provided as empty arrays, we must return [] so caller can detach.
        $tagsInputProvided = $tagIds !== null || $tagNames !== null;

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

        if (! $tagsInputProvided) {
            return null;
        }

        return $tagIds;
    }
}
