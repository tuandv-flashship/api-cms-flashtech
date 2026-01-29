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
    public function makeImporter(string $type, ?string $class = null): Importer
    {
        return match ($type) {
            'posts' => app(PostsImporter::class),
            'post-translations' => $this->makePostTranslationsImporter($class),
            'other-translations' => app(OtherTranslationsImporter::class),
            default => throw new InvalidArgumentException('Unsupported import type.'),
        };
    }

    public function makeExporter(string $type, ?string $class = null): Exporter
    {
        return match ($type) {
            'posts' => app(PostsExporter::class),
            'post-translations' => $this->makePostTranslationsExporter($class),
            'other-translations' => app(OtherTranslationsExporter::class),
            default => throw new InvalidArgumentException('Unsupported export type.'),
        };
    }

    private function makePostTranslationsImporter(?string $class): Importer
    {
        $this->guardPostClass($class);

        return app(PostTranslationsImporter::class);
    }

    private function makePostTranslationsExporter(?string $class): Exporter
    {
        $this->guardPostClass($class);

        return app(PostTranslationsExporter::class);
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
}
