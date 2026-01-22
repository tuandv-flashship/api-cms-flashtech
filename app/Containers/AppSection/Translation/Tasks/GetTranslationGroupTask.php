<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class GetTranslationGroupTask extends ParentTask
{
    public function __construct(private readonly TranslationFilesystem $filesystem)
    {
    }

    /**
     * @return array<string, string>
     */
    public function run(string $locale, string $group): array
    {
        return $this->filesystem->readTranslations($locale, $group);
    }
}
