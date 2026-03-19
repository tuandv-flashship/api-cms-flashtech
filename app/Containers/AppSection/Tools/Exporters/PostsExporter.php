<?php

namespace App\Containers\AppSection\Tools\Exporters;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Tools\Supports\Export\ExportColumn;
use App\Containers\AppSection\Tools\Supports\Export\Exporter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

final class PostsExporter extends Exporter
{
    /**
     * @var array<string, mixed>
     */
    private array $filters = [];

    /**
     * @param array<string, mixed> $filters
     */
    public function withFilters(array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * @return array<int, ExportColumn>
     */
    public function columns(): array
    {
        return [
            ExportColumn::make('name')->label('Name'),
            ExportColumn::make('description')->label('Description'),
            ExportColumn::make('content')->label('Content'),
            ExportColumn::make('is_featured')->label('Is Featured'),
            ExportColumn::make('format_type')->label('Format Type'),
            ExportColumn::make('image')->label('Image'),
            ExportColumn::make('views')->label('Views'),
            ExportColumn::make('slug')->label('Slug'),
            ExportColumn::make('url')->label('URL'),
            ExportColumn::make('status')->label('Status'),
            ExportColumn::make('categories')->label('Categories'),
            ExportColumn::make('tags')->label('Tags'),
        ];
    }

    public function getTotal(): int
    {
        return Post::query()->count();
    }

    public function getFilterSchema(): array
    {
        $statusOptions = array_map(
            static fn (ContentStatus $s) => ['value' => $s->value, 'label' => ucfirst($s->value)],
            ContentStatus::cases()
        );

        $categoryOptions = Category::query()
            ->select(['id', 'name'])
            ->orderBy('name')
            ->get()
            ->map(static fn ($c) => ['value' => $c->getHashedKey(), 'label' => $c->name])
            ->all();

        return [
            ['key' => 'limit', 'type' => 'number', 'label' => 'Limit', 'placeholder' => 'Leave empty to export all'],
            ['key' => 'status', 'type' => 'select', 'label' => 'Status', 'options' => $statusOptions],
            ['key' => 'is_featured', 'type' => 'select', 'label' => 'Is Featured', 'options' => [
                ['value' => '1', 'label' => 'Yes'],
                ['value' => '0', 'label' => 'No'],
            ]],
            ['key' => 'category_id', 'type' => 'select', 'label' => 'Category', 'options' => $categoryOptions],
            ['key' => 'start_date', 'type' => 'date', 'label' => 'Start Date'],
            ['key' => 'end_date', 'type' => 'date', 'label' => 'End Date'],
        ];
    }

    public function getFilterValidationRules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(array_map(static fn (ContentStatus $s) => $s->value, ContentStatus::cases()))],
            'is_featured' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ];
    }

    public function getDecodeFields(): array
    {
        return ['category_id'];
    }

    /**
     * @return iterable<int, array<string, mixed>>
     */
    protected function getRows(): iterable
    {
        $query = Post::query()
            ->with(['categories', 'tags', 'slugable']);

        $this->applyFilters($query);

        // Use cursor for memory efficiency on large datasets.
        foreach ($query->cursor() as $post) {
            yield [
                'name' => $post->name,
                'description' => $post->description,
                'content' => $post->content,
                'is_featured' => $post->is_featured ? 'Yes' : 'No',
                'format_type' => $post->format_type,
                'image' => $post->image,
                'views' => $post->views,
                'slug' => $post->slug,
                'url' => $post->url,
                'status' => $post->status?->value ?? (string) $post->status,
                'categories' => $post->categories->pluck('name')->implode(', '),
                'tags' => $post->tags->pluck('name')->implode(', '),
            ];
        }
    }

    private function applyFilters(Builder $query): void
    {
        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (array_key_exists('is_featured', $this->filters) && $this->filters['is_featured'] !== '') {
            $query->where('is_featured', (bool) $this->filters['is_featured']);
        }

        if (! empty($this->filters['category_id'])) {
            $categoryId = (int) $this->filters['category_id'];
            $query->whereHas('categories', static fn (Builder $builder) => $builder->where('categories.id', $categoryId));
        }

        if (! empty($this->filters['start_date'])) {
            $start = Carbon::parse($this->filters['start_date'])->startOfDay();
            $query->where('created_at', '>=', $start);
        }

        if (! empty($this->filters['end_date'])) {
            $end = Carbon::parse($this->filters['end_date'])->endOfDay();
            $query->where('created_at', '<=', $end);
        }

        if (! empty($this->filters['limit'])) {
            $query->limit((int) $this->filters['limit']);
        }
    }
}
