<?php

namespace App\Containers\AppSection\Tools\Supports;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Tools\Exporters\OtherTranslationsExporter;
use App\Containers\AppSection\Tools\Exporters\PageTranslationsExporter;
use App\Containers\AppSection\Tools\Exporters\PagesExporter;
use App\Containers\AppSection\Tools\Exporters\PostTranslationsExporter;
use App\Containers\AppSection\Tools\Exporters\PostsExporter;
use App\Containers\AppSection\Tools\Importers\OtherTranslationsImporter;
use App\Containers\AppSection\Tools\Importers\PageTranslationsImporter;
use App\Containers\AppSection\Tools\Importers\PagesImporter;
use App\Containers\AppSection\Tools\Importers\PostTranslationsImporter;
use App\Containers\AppSection\Tools\Importers\PostsImporter;
use App\Containers\AppSection\Tools\Supports\Export\Exporter;
use App\Containers\AppSection\Tools\Supports\Import\Importer;
use InvalidArgumentException;

final class DataSynchronizeRegistry
{
    /** @var array<string, Importer> */
    private array $importers;

    /** @var array<string, Exporter> */
    private array $exporters;

    public function __construct(
        PostsImporter $postsImporter,
        PostTranslationsImporter $postTranslationsImporter,
        OtherTranslationsImporter $otherTranslationsImporter,
        PagesImporter $pagesImporter,
        PageTranslationsImporter $pageTranslationsImporter,
        PostsExporter $postsExporter,
        PostTranslationsExporter $postTranslationsExporter,
        OtherTranslationsExporter $otherTranslationsExporter,
        PagesExporter $pagesExporter,
        PageTranslationsExporter $pageTranslationsExporter,
    ) {
        $this->importers = [
            'posts' => $postsImporter,
            'post-translations' => $postTranslationsImporter,
            'other-translations' => $otherTranslationsImporter,
            'pages' => $pagesImporter,
            'page-translations' => $pageTranslationsImporter,
        ];

        $this->exporters = [
            'posts' => $postsExporter,
            'post-translations' => $postTranslationsExporter,
            'other-translations' => $otherTranslationsExporter,
            'pages' => $pagesExporter,
            'page-translations' => $pageTranslationsExporter,
        ];
    }

    public function makeImporter(string $type): Importer
    {
        if (! isset($this->importers[$type])) {
            throw new InvalidArgumentException("Unsupported import type: {$type}");
        }

        return clone $this->importers[$type];
    }

    public function makeExporter(string $type): Exporter
    {
        if (! isset($this->exporters[$type])) {
            throw new InvalidArgumentException("Unsupported export type: {$type}");
        }

        return clone $this->exporters[$type];
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableTypes(): array
    {
        return array_keys($this->exporters);
    }

    public function hasType(string $type): bool
    {
        return isset($this->exporters[$type]);
    }

    public function getExportPermission(string $type): string
    {
        return "{$type}.export";
    }

    public function getImportPermission(string $type): string
    {
        return "{$type}.import";
    }
}
