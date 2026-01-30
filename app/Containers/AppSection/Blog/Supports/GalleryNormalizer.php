<?php

namespace App\Containers\AppSection\Blog\Supports;

final class GalleryNormalizer
{
    /**
     * Normalize gallery input to consistent array format.
     *
     * @param array<int, array<string, mixed>>|string|null $gallery
     * @return array<int, array<string, mixed>>|null
     */
    public static function normalize(array|string|null $gallery): ?array
    {
        if ($gallery === null) {
            return null;
        }

        if (is_string($gallery)) {
            $decoded = json_decode($gallery, true);
            if (! is_array($decoded)) {
                return null;
            }
            $gallery = $decoded;
        }

        $items = [];
        foreach ($gallery as $item) {
            if (! is_array($item)) {
                continue;
            }

            $image = $item['img'] ?? $item['image'] ?? null;
            if (! is_string($image) || trim($image) === '') {
                continue;
            }

            $items[] = [
                'img' => $image,
                'description' => isset($item['description']) ? (string) $item['description'] : null,
            ];
        }

        return $items;
    }
}
