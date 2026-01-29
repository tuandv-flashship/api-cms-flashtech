<?php

namespace App\Containers\AppSection\Tools\Importers;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\Blog\Supports\PostFormat;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Containers\AppSection\Tools\Supports\Import\ImportColumn;
use App\Containers\AppSection\Tools\Supports\Import\Importer;
use App\Containers\AppSection\User\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

final class PostsImporter extends Importer
{
    /**
     * @return array<int, ImportColumn>
     */
    public function columns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->rules(['required', 'string', 'max:250']),
            ImportColumn::make('slug')
                ->label('Slug')
                ->rules(['nullable', 'string', 'max:250']),
            ImportColumn::make('description')
                ->label('Description')
                ->rules(['nullable', 'string', 'max:400']),
            ImportColumn::make('content')
                ->label('Content')
                ->rules(['nullable', 'string', 'max:300000']),
            ImportColumn::make('tags')
                ->label('Tags')
                ->rules(['sometimes', 'array']),
            ImportColumn::make('categories')
                ->label('Categories')
                ->rules(['sometimes', 'array']),
            ImportColumn::make('status')
                ->label('Status')
                ->rules(['nullable', Rule::in(array_map(static fn (ContentStatus $status) => $status->value, ContentStatus::cases()))]),
            ImportColumn::make('is_featured')
                ->label('Is Featured')
                ->boolean()
                ->rules(['boolean']),
            ImportColumn::make('image')
                ->label('Image')
                ->rules(['nullable', 'string']),
            ImportColumn::make('format_type')
                ->label('Format Type')
                ->rules(['nullable', 'string', 'max:50', Rule::in(array_keys(PostFormat::all()))]),
        ];
    }

    public function chunkSize(): int
    {
        return (int) config('data-synchronize.imports.posts_chunk_size', 50);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function examples(): array
    {
        $posts = Post::query()
            ->with(['categories', 'tags', 'slugable'])
            ->take(5)
            ->get()
            ->map(function (Post $post): array {
                return [
                    'name' => $post->name,
                    'slug' => $post->slugable?->key ?? '',
                    'description' => $post->description,
                    'content' => $post->content,
                    'tags' => $post->tags->pluck('name')->implode(', '),
                    'categories' => $post->categories->pluck('name')->implode(', '),
                    'status' => $post->status?->value ?? (string) $post->status,
                    'is_featured' => $post->is_featured ? 'Yes' : 'No',
                    'image' => $post->image,
                    'format_type' => $post->format_type,
                ];
            })
            ->all();

        return $posts !== [] ? $posts : [
            [
                'name' => 'Example post',
                'slug' => 'example-post',
                'description' => 'Example description',
                'content' => 'Example content',
                'tags' => 'Tech, News',
                'categories' => 'News',
                'status' => ContentStatus::PUBLISHED->value,
                'is_featured' => 'No',
                'image' => 'https://example.com/example.jpg',
                'format_type' => '',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    protected function mapRow(array $row): array
    {
        return [
            ...$row,
            'tags' => $this->parseList(Arr::get($row, 'tags')),
            'categories' => $this->parseList(Arr::get($row, 'categories')),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function handle(array $rows): int
    {
        $slugHelper = app(SlugHelper::class);
        $count = 0;

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $slug = $this->normalizeString($row['slug'] ?? null);
            $status = $this->normalizeStatus($row['status'] ?? null);
            $formatType = $this->normalizeFormatType($row['format_type'] ?? null);

            $post = Post::query()->firstOrCreate(
                ['name' => $name],
                [
                    'description' => $this->normalizeString($row['description'] ?? null),
                    'content' => $this->normalizeString($row['content'] ?? null),
                    'status' => $status,
                    'is_featured' => (bool) ($row['is_featured'] ?? false),
                    'image' => $this->normalizeString($row['image'] ?? null),
                    'format_type' => $formatType,
                    'author_id' => auth()->id(),
                    'author_type' => User::class,
                ]
            );

            if ($post->wasRecentlyCreated) {
                $slugHelper->createSlug($post, $slug);
                $count++;
            }

            $categoryIds = $this->resolveCategories($row['categories'] ?? [], $slugHelper);
            if ($categoryIds !== []) {
                $post->categories()->sync($categoryIds);
            }

            $tagIds = $this->resolveTags($row['tags'] ?? [], $slugHelper);
            if ($tagIds !== []) {
                $post->tags()->sync($tagIds);
            }
        }

        return $count;
    }

    /**
     * @param mixed $value
     * @return array<int, string>
     */
    private function parseList(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), static fn ($item) => $item !== ''));
        }

        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), static fn ($item) => $item !== ''));
    }

    private function normalizeString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' ? null : (is_string($value) ? $value : null);
    }

    private function normalizeStatus(mixed $value): ContentStatus
    {
        $value = is_string($value) ? strtolower(trim($value)) : '';
        foreach (ContentStatus::cases() as $status) {
            if ($status->value === $value) {
                return $status;
            }
        }

        return ContentStatus::PUBLISHED;
    }

    private function normalizeFormatType(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '') {
            return null;
        }

        $formats = array_keys(PostFormat::all());

        return in_array($value, $formats, true) ? $value : null;
    }

    /**
     * @param array<int, string> $names
     * @return array<int, int>
     */
    private function resolveCategories(array $names, SlugHelper $slugHelper): array
    {
        $ids = [];

        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $category = Category::query()->firstOrCreate(
                ['name' => $name],
                ['status' => ContentStatus::PUBLISHED]
            );

            if ($category->wasRecentlyCreated) {
                $slugHelper->createSlug($category);
            }

            $ids[] = $category->getKey();
        }

        return array_values(array_unique($ids));
    }

    /**
     * @param array<int, string> $names
     * @return array<int, int>
     */
    private function resolveTags(array $names, SlugHelper $slugHelper): array
    {
        $ids = [];

        foreach ($names as $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }

            $tag = Tag::query()->firstOrCreate(
                ['name' => $name],
                ['status' => ContentStatus::PUBLISHED]
            );

            if ($tag->wasRecentlyCreated) {
                $slugHelper->createSlug($tag);
            }

            $ids[] = $tag->getKey();
        }

        return array_values(array_unique($ids));
    }
}
