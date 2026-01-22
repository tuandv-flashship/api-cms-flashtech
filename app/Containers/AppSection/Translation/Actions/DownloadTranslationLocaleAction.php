<?php

namespace App\Containers\AppSection\Translation\Actions;

use App\Containers\AppSection\Translation\Tasks\CreateTranslationArchiveTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DownloadTranslationLocaleAction extends ParentAction
{
    public function __construct(private readonly CreateTranslationArchiveTask $createTranslationArchiveTask)
    {
    }

    public function run(string $locale): string
    {
        return $this->createTranslationArchiveTask->run($locale);
    }
}
