<?php

namespace App\Containers\AppSection\CustomField\Supports;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Supports\PostFormat;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Supports\PageOptions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class CustomFieldRules
{
    public static function ruleKey(string $slug): string
    {
        return Str::slug(str_replace('\\', '_', $slug), '_');
    }

    /**
     * @return array<string, array<int, array<string, mixed>>>
     */
    public static function groups(): array
    {
        return [
            'basic' => [
                [
                    'slug' => 'page_template',
                    'label' => __('custom-field.options.rules.page_template'),
                    'data' => PageOptions::templateOptions(),
                ],
                [
                    'slug' => Page::class,
                    'label' => __('custom-field.options.rules.page'),
                    'source' => [
                        'endpoint' => '/v1/pages',
                        'label_key' => 'name',
                        'value_key' => 'id',
                    ],
                ],
            ],
            'blog' => [
                [
                    'slug' => Category::class,
                    'label' => __('custom-field.options.rules.category'),
                    'source' => [
                        'endpoint' => '/v1/blog/categories',
                        'label_key' => 'name',
                        'value_key' => 'id',
                    ],
                ],
                [
                    'slug' => Post::class . '_post_with_related_category',
                    'label' => __('custom-field.options.rules.post_with_related_category'),
                    'source' => [
                        'endpoint' => '/v1/blog/categories',
                        'label_key' => 'name',
                        'value_key' => 'id',
                    ],
                ],
                [
                    'slug' => Post::class . '_post_format',
                    'label' => __('custom-field.options.rules.post_format'),
                    'data' => self::normalizePairs(PostFormat::toPairs()),
                ],
            ],
            'other' => [
                [
                    'slug' => 'logged_in_user',
                    'label' => __('custom-field.options.rules.logged_in_user'),
                    'source' => [
                        'endpoint' => '/v1/users',
                        'label_key' => 'name',
                        'value_key' => 'id',
                    ],
                ],
                [
                    'slug' => 'logged_in_user_has_role',
                    'label' => __('custom-field.options.rules.logged_in_user_has_role'),
                    'source' => [
                        'endpoint' => '/v1/roles',
                        'label_key' => 'name',
                        'value_key' => 'id',
                    ],
                ],
                [
                    'slug' => 'model_name',
                    'label' => __('custom-field.options.rules.model_name'),
                    'data' => self::modelNameOptions(),
                ],
            ],
        ];
    }

    /**
     * @param array<int, array{0: string, 1: string}> $pairs
     * @return array<int, array{value: string, label: string}>
     */
    private static function normalizePairs(array $pairs): array
    {
        $options = [];

        foreach ($pairs as $pair) {
            $value = (string) ($pair[0] ?? '');
            $label = (string) ($pair[1] ?? $value);

            if ($value === '') {
                continue;
            }

            $options[] = [
                'value' => $value,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private static function modelNameOptions(): array
    {
        $options = [];
        $supported = (array) config('custom-field.supported', []);

        foreach ($supported as $key => $class) {
            $labelKey = 'custom-field.options.rules.model_name_' . $key;
            $label = __($labelKey);
            if ($label === $labelKey) {
                $label = Str::title(str_replace(['-', '_'], ' ', (string) $key));
            }

            $options[] = [
                'value' => (string) $class,
                'label' => $label,
            ];
        }

        return $options;
    }

    /**
     * @param array<string, mixed> $extraRules
     * @return array<string, mixed>
     */
    public static function buildContext(?Model $model, string $referenceType, array $extraRules = []): array
    {
        $context = [
            self::ruleKey('model_name') => $referenceType,
        ];

        $user = auth()->user();
        $context[self::ruleKey('logged_in_user')] = $user?->getKey();
        $context[self::ruleKey('logged_in_user_has_role')] = $user
            ? $user->roles?->pluck('name')->all() ?? []
            : [];

        if ($model instanceof Page) {
            $context[self::ruleKey('page_template')] = $model->template;
            $context[self::ruleKey(Page::class)] = $model->getKey();
        }

        if ($model instanceof Category) {
            $context[self::ruleKey(Category::class)] = $model->getKey();
        }

        if ($model instanceof Post) {
            $context[self::ruleKey(Post::class . '_post_format')] = $model->format_type;
            $context[self::ruleKey(Post::class . '_post_with_related_category')] = $model->categories->pluck('id')->all();
        }

        foreach ($extraRules as $key => $value) {
            if (! is_string($key)) {
                continue;
            }

            $context[self::ruleKey($key)] = $value;
        }

        return $context;
    }

    /**
     * @param array<int, array<string, mixed>> $rules
     */
    public static function normalizeRules(array $rules): array
    {
        $normalized = [];

        foreach ($rules as $rule) {
            if (! is_array($rule)) {
                continue;
            }

            $name = Arr::get($rule, 'name');
            if (! is_string($name) || $name === '') {
                continue;
            }

            $rule['name'] = self::ruleKey($name);
            $normalized[] = $rule;
        }

        return $normalized;
    }
}
