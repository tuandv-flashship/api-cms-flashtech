<?php

namespace App\Containers\AppSection\Translation\Actions;

use App\Containers\AppSection\Translation\Tasks\DeleteTranslationLocaleTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeleteTranslationLocaleAction extends ParentAction
{
    public function __construct(private readonly DeleteTranslationLocaleTask $deleteTranslationLocaleTask)
    {
    }

    public function run(string $locale): void
    {
        $this->deleteTranslationLocaleTask->run($locale);
    }
}
