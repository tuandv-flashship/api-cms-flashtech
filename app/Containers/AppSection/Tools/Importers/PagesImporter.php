<?php

namespace App\Containers\AppSection\Tools\Importers;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Slug\Supports\SlugHelper;
use App\Containers\AppSection\Tools\Supports\Concerns\ImportNormalizationHelper;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeStorage;
use App\Containers\AppSection\Tools\Supports\Import\ImportColumn;
use App\Containers\AppSection\Tools\Supports\Import\Importer;
use App\Containers\AppSection\Tools\Supports\SpreadsheetReader;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

final class PagesImporter extends Importer
{
    use ImportNormalizationHelper;
    public function __construct(
        SpreadsheetReader $reader,
        DataSynchronizeStorage $storage,
        private readonly SlugHelper $slugHelper,
    ) {
        parent::__construct($reader, $storage);
    }

    /**
     * @return array<int, ImportColumn>
     */
    public function columns(): array
    {
        return [
            ImportColumn::make('name')
                ->label('Name')
                ->rules(['required', 'string', 'max:250']),
            ImportColumn::make('description')
                ->label('Description')
                ->rules(['nullable', 'string', 'max:400']),
            ImportColumn::make('content')
                ->label('Content')
                ->rules(['nullable', 'string', 'max:300000']),
            ImportColumn::make('image')
                ->label('Image')
                ->rules(['nullable', 'string']),
            ImportColumn::make('template')
                ->label('Template')
                ->rules(['nullable', 'string', 'max:60']),
            ImportColumn::make('slug')
                ->label('Slug')
                ->rules(['nullable', 'string', 'max:250']),
            ImportColumn::make('status')
                ->label('Status')
                ->rules(['nullable', Rule::in(array_map(static fn (ContentStatus $status) => $status->value, ContentStatus::cases()))]),
        ];
    }

    public function chunkSize(): int
    {
        return (int) config('data-synchronize.imports.pages_chunk_size', 50);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function examples(): array
    {
        $pages = Page::query()
            ->with(['slugable'])
            ->take(5)
            ->get()
            ->map(function (Page $page): array {
                return [
                    'name' => $page->name,
                    'description' => $page->description,
                    'content' => $page->content,
                    'image' => $page->image,
                    'template' => $page->template,
                    'slug' => $page->slugable?->key ?? '',
                    'status' => $page->status?->value ?? (string) $page->status,
                ];
            })
            ->all();

        return $pages !== [] ? $pages : [
            [
                'name' => 'About Us',
                'description' => 'Learn more about our company',
                'content' => 'Welcome to our company.',
                'image' => '',
                'template' => 'default',
                'slug' => 'about-us',
                'status' => ContentStatus::PUBLISHED->value,
            ],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function handle(array $rows): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $slug = $this->normalizeString($row['slug'] ?? null);
            $status = $this->normalizeStatus($row['status'] ?? null);

            $page = Page::query()->firstOrCreate(
                ['name' => $name],
                [
                    'description' => $this->normalizeString($row['description'] ?? null),
                    'content' => $this->normalizeString($row['content'] ?? null),
                    'image' => $this->normalizeString($row['image'] ?? null),
                    'template' => $this->normalizeString($row['template'] ?? null) ?? 'default',
                    'status' => $status,
                    'user_id' => auth()->id(),
                ]
            );

            if ($page->wasRecentlyCreated) {
                $this->slugHelper->createSlug($page, $slug);
                $count++;
            }
        }

        return $count;
    }


}
