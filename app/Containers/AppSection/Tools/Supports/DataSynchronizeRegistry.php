<?php

namespace App\Containers\AppSection\Tools\Supports;

use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\Tools\Exporters\OtherTranslationsExporter;
use App\Containers\AppSection\Tools\Exporters\PostTranslationsExporter;
use App\Containers\AppSection\Tools\Exporters\PostsExporter;
use App\Containers\AppSection\Tools\Importers\OtherTranslationsImporter;
use App\Containers\AppSection\Tools\Importers\PostTranslationsImporter;
use App\Containers\AppSection\Tools\Importers\PostsImporter;
use App\Containers\AppSection\Tools\Supports\Export\Exporter;
use App\Containers\AppSection\Tools\Supports\Import\Importer;
use InvalidArgumentException;

final class DataSynchronizeRegistry
{
    public function __construct(
        private readonly PostsImporter $postsImporter,
        private readonly PostTranslationsImporter $postTranslationsImporter,
        private readonly OtherTranslationsImporter $otherTranslationsImporter,
        private readonly PostsExporter $postsExporter,
        private readonly PostTranslationsExporter $postTranslationsExporter,
        private readonly OtherTranslationsExporter $otherTranslationsExporter,
    ) {
    }

    public function makeImporter(string $type, ?string $class = null): Importer
    {
        return match ($type) {
            'posts' => $this->freshImporter($this->postsImporter),
            'post-translations' => $this->makePostTranslationsImporter($class),
            'other-translations' => $this->freshImporter($this->otherTranslationsImporter),
            default => throw new InvalidArgumentException('Unsupported import type.'),
        };
    }

    public function makeExporter(string $type, ?string $class = null): Exporter
    {
        return match ($type) {
            'posts' => $this->freshExporter($this->postsExporter),
            'post-translations' => $this->makePostTranslationsExporter($class),
            'other-translations' => $this->freshExporter($this->otherTranslationsExporter),
            default => throw new InvalidArgumentException('Unsupported export type.'),
        };
    }

    private function makePostTranslationsImporter(?string $class): Importer
    {
        $this->guardPostClass($class);

        return $this->freshImporter($this->postTranslationsImporter);
    }

    private function makePostTranslationsExporter(?string $class): Exporter
    {
        $this->guardPostClass($class);

        return $this->freshExporter($this->postTranslationsExporter);
    }

    private function guardPostClass(?string $class): void
    {
        if ($class === null || $class === '') {
            return;
        }

        if ($class !== Post::class) {
            throw new InvalidArgumentException('Unsupported translation class.');
        }
    }

    private function freshImporter(Importer $importer): Importer
    {
        return clone $importer;
    }

    private function freshExporter(Exporter $exporter): Exporter
    {
        return clone $exporter;
    }
}
