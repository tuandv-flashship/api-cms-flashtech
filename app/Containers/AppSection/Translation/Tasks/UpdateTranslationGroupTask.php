<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class UpdateTranslationGroupTask extends ParentTask
{
    public function __construct(private readonly TranslationFilesystem $filesystem)
    {
    }

    /**
     * @param array<string, mixed> $translations
     * @return array<string, string>
     */
    public function run(string $locale, string $group, array $translations): array
    {
        return $this->filesystem->writeTranslations($locale, $group, $translations);
    }
}
