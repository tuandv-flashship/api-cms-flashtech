<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ListTranslationGroupsTask extends ParentTask
{
    public function __construct(private readonly TranslationFilesystem $filesystem)
    {
    }

    /**
     * @return array<int, string>
     */
    public function run(string $locale): array
    {
        return $this->filesystem->listGroups($locale);
    }
}
