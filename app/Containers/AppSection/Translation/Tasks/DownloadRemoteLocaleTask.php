<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

final class DownloadRemoteLocaleTask extends ParentTask
{
    public function __construct(
        private readonly TranslationFilesystem $filesystem,
        private readonly GetRemoteLocalesTask $getRemoteLocalesTask,
    ) {
    }

    public function run(string $locale, bool $includeVendor = true): bool
    {
        $normalized = $this->filesystem->normalizeLocale($locale);

        if ($normalized === 'en' || File::exists($this->filesystem->langPath($normalized))) {
            return true;
        }

        $available = $this->getRemoteLocalesTask->run();
        if (! in_array($normalized, $available, true)) {
            return false;
        }

        $repository = config('appSection-translation.repository', 'botble/translations');
        $branch = config('appSection-translation.branch', 'develop');
        $repoName = Str::afterLast($repository, '/');
        $safeBranch = str_replace('/', '-', $branch);

        $destination = storage_path('app/translations.zip');
        $extractRoot = storage_path('app');
        $extractPath = $extractRoot . DIRECTORY_SEPARATOR . $repoName . '-' . $safeBranch;

        $this->filesystem->ensureLangPath();
        File::ensureDirectoryExists($extractRoot);

        Http::withoutVerifying()
            ->timeout(300)
            ->sink($destination)
            ->get(sprintf('https://github.com/%s/archive/refs/heads/%s.zip', $repository, $branch))
            ->throw();

        $zip = new \ZipArchive();
        if ($zip->open($destination) !== true) {
            File::delete($destination);
            throw new RuntimeException('Unable to open translations archive.');
        }

        $zip->extractTo($extractRoot);
        $zip->close();

        $localePath = $extractPath . DIRECTORY_SEPARATOR . $normalized;
        if (! File::isDirectory($localePath)) {
            File::delete($destination);
            File::deleteDirectory($extractPath);

            return false;
        }

        $localeSource = $localePath;
        $nestedLocalePath = $localePath . DIRECTORY_SEPARATOR . $normalized;
        if (File::isDirectory($nestedLocalePath)) {
            $localeSource = $nestedLocalePath;
        }

        File::copyDirectory($localeSource, $this->filesystem->langPath($normalized));

        $jsonCandidates = [
            $localePath . DIRECTORY_SEPARATOR . $normalized . '.json',
            $localePath . '.json',
            $extractPath . DIRECTORY_SEPARATOR . $normalized . '.json',
        ];

        $copiedJson = false;
        foreach ($jsonCandidates as $candidate) {
            if (File::exists($candidate)) {
                File::copy($candidate, $this->filesystem->langPath($normalized . '.json'));
                $copiedJson = true;
                break;
            }
        }

        $nestedJsonTarget = $this->filesystem->langPath(
            $normalized . DIRECTORY_SEPARATOR . $normalized . '.json'
        );
        if ($copiedJson && File::exists($nestedJsonTarget)) {
            File::delete($nestedJsonTarget);
        }

        if ($includeVendor) {
            $vendorPath = $extractPath . DIRECTORY_SEPARATOR . 'vendor';
            if (File::isDirectory($vendorPath)) {
                File::copyDirectory($vendorPath, $this->filesystem->langPath('vendor'));
            }
        }

        File::delete($destination);
        File::deleteDirectory($extractPath);

        return true;
    }
}
