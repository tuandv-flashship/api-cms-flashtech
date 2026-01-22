<?php

namespace App\Containers\AppSection\Translation\Tasks;

use App\Containers\AppSection\Translation\Supports\TranslationFilesystem;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteTranslationLocaleTask extends ParentTask
{
    public function __construct(private readonly TranslationFilesystem $filesystem)
    {
    }

    public function run(string $locale): void
    {
        $this->filesystem->deleteLocale($locale);
    }
}
