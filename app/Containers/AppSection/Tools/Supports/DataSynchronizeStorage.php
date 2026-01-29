<?php

namespace App\Containers\AppSection\Tools\Supports;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class DataSynchronizeStorage
{
    public function disk(): Filesystem
    {
        return Storage::disk($this->diskName());
    }

    public function path(string $fileName): string
    {
        return $this->disk()->path($this->relativePath($fileName));
    }

    public function relativePath(string $fileName): string
    {
        return $this->basePath() . '/' . ltrim($fileName, '/');
    }

    public function exists(string $fileName): bool
    {
        return $this->disk()->exists($this->relativePath($fileName));
    }

    public function delete(string $fileName): void
    {
        $this->disk()->delete($this->relativePath($fileName));
    }

    /**
     * @return array{file_name: string, original_name: string, size: int, mime_type: string|null}
     */
    public function store(UploadedFile $file): array
    {
        $extension = $file->getClientOriginalExtension();
        $extension = $extension !== '' ? $extension : $file->guessExtension();
        $extension = $extension ?: 'csv';

        $fileName = sprintf(
            '%s-%s.%s',
            Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'import',
            Str::random(12),
            strtolower($extension)
        );

        $size = (int) $file->getSize();
        $mimeType = $file->getMimeType();

        $destination = $this->disk()->path($this->basePath());
        File::ensureDirectoryExists($destination);

        $file->move($destination, $fileName);

        return [
            'file_name' => $fileName,
            'original_name' => $file->getClientOriginalName(),
            'size' => $size,
            'mime_type' => $mimeType,
        ];
    }

    private function diskName(): string
    {
        return (string) config('data-synchronize.storage.disk', 'local');
    }

    private function basePath(): string
    {
        return trim((string) config('data-synchronize.storage.path', 'data-synchronize'), '/');
    }
}
