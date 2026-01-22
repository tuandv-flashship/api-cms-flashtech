<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\File;
use RuntimeException;

final class CreateTranslationArchiveTask extends ParentTask
{
    public function __construct(private readonly TranslationFilesystem $filesystem)
    {
    }

    public function run(string $locale): string
    {
        $files = $this->filesystem->collectLocaleFiles($locale);
        if ($files === []) {
            throw new RuntimeException('Locale translations not found.');
        }

        $archivePath = storage_path('app/locale-' . $this->filesystem->normalizeLocale($locale) . '.zip');
        File::ensureDirectoryExists(dirname($archivePath));

        $zip = new \ZipArchive();
        if ($zip->open($archivePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create translations archive.');
        }

        $basePath = rtrim($this->filesystem->langPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        foreach ($files as $file) {
            $relative = str_replace($basePath, '', $file);
            $zip->addFile($file, $relative);
        }

        $zip->close();

        return $archivePath;
    }
}
