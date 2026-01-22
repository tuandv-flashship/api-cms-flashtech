<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Throwable;

final class CreateTranslationLocaleTask extends ParentTask
{
    public function __construct(
        private readonly TranslationFilesystem $filesystem,
        private readonly DownloadRemoteLocaleTask $downloadRemoteLocaleTask,
    ) {
    }

    /**
     * @return array{locale: string, downloaded: bool, copied: bool}
     */
    public function run(string $locale, string $source = 'github', bool $includeVendor = true): array
    {
        $normalized = $this->filesystem->normalizeLocale($locale);
        $downloaded = false;

        if ($source === 'github') {
            try {
                $downloaded = $this->downloadRemoteLocaleTask->run($normalized, $includeVendor);
            } catch (Throwable) {
                $downloaded = false;
            }
        }

        $copied = false;
        if (! $downloaded) {
            $copied = $this->filesystem->copyLocaleFromDefault($normalized);
        }

        return [
            'locale' => $normalized,
            'downloaded' => $downloaded,
            'copied' => $copied,
        ];
    }
}
