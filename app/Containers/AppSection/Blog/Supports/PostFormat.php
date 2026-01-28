<?php

namespace App\Containers\AppSection\Blog\Supports;

final class PostFormat
{
    /**
     * @var array<string, array{key: string, icon: string|null, name: string}>
     */
    private static array $formats = [
        '' => [
            'key' => '',
            'icon' => null,
            'name' => 'blog.options.format_types.default',
        ],
    ];

    /**
     * @param array<string, array{key?: string, icon?: string|null, name?: string}> $formats
     */
    public static function registerPostFormat(array $formats = []): void
    {
        foreach ($formats as $key => $format) {
            $payload = array_merge($format, [
                'key' => $format['key'] ?? $key,
                'icon' => $format['icon'] ?? null,
                'name' => $format['name'] ?? $key,
            ]);

            self::$formats[$key] = $payload;
        }
    }

    /**
     * @return array<string, array{key: string, icon: string|null, name: string}>
     */
    public static function all(): array
    {
        return self::$formats;
    }

    /**
     * @return array<int, array{0: string, 1: string}>
     */
    public static function toPairs(): array
    {
        $results = [];
        foreach (self::$formats as $key => $item) {
            $label = (string) $item['name'];
            $results[] = [
                (string) $key,
                str_contains($label, '::') || str_starts_with($label, 'blog.')
                    ? __($label)
                    : $label,
            ];
        }

        return $results;
    }
}
