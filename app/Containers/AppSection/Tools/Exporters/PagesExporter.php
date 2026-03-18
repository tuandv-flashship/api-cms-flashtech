<?php

namespace App\Containers\AppSection\Tools\Exporters;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Tools\Supports\Export\ExportColumn;
use App\Containers\AppSection\Tools\Supports\Export\Exporter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

final class PagesExporter extends Exporter
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
            ExportColumn::make('image')->label('Image'),
            ExportColumn::make('template')->label('Template'),
            ExportColumn::make('slug')->label('Slug'),
            ExportColumn::make('status')->label('Status'),
        ];
    }

    public function getTotal(): int
    {
        return Page::query()->count();
    }

    public function getFilterSchema(): array
    {
        return [
            ['key' => 'limit', 'type' => 'number', 'label' => 'Limit', 'placeholder' => 'Leave empty to export all'],
            ['key' => 'status', 'type' => 'select', 'label' => 'Status', 'options' => array_map(
                static fn (ContentStatus $s) => ['value' => $s->value, 'label' => ucfirst($s->value)],
                ContentStatus::cases()
            )],
            ['key' => 'template', 'type' => 'select', 'label' => 'Template', 'options' => []],
            ['key' => 'start_date', 'type' => 'date', 'label' => 'Start Date'],
            ['key' => 'end_date', 'type' => 'date', 'label' => 'End Date'],
        ];
    }

    public function getFilterValidationRules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(array_map(static fn (ContentStatus $s) => $s->value, ContentStatus::cases()))],
            'template' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ];
    }

    /**
     * @return iterable<int, array<string, mixed>>
     */
    protected function getRows(): iterable
    {
        $query = Page::query()
            ->with(['slugable']);

        $this->applyFilters($query);

        // Use cursor for memory efficiency on large datasets.
        foreach ($query->cursor() as $page) {
            yield [
                'name' => $page->name,
                'description' => $page->description,
                'content' => $page->content,
                'image' => $page->image,
                'template' => $page->template,
                'slug' => $page->slugable?->key ?? '',
                'status' => $page->status?->value ?? (string) $page->status,
            ];
        }
    }

    private function applyFilters(Builder $query): void
    {
        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['template']) && $this->filters['template'] !== '') {
            $query->where('template', $this->filters['template']);
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
