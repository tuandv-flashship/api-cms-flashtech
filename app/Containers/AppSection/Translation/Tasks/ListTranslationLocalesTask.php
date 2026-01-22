<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ListTranslationLocalesTask extends ParentTask
{
    public function __construct(private readonly TranslationFilesystem $filesystem)
    {
    }

    /**
     * @return array{installed: array<int, array<string, mixed>>, available: array<int, array<string, mixed>>}
     */
    public function run(): array
    {
        $installed = $this->filesystem->listInstalledLocales();

        return [
            'installed' => $this->filesystem->mapInstalledLocales($installed),
            'available' => $this->filesystem->listAvailableLocales($installed),
        ];
    }
}
