<?php

namespace App\Containers\AppSection\Language\Actions;

use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Language\Tasks\GetCurrentLanguageTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class GetCurrentLanguageAction extends ParentAction
{
    public function __construct(
        private readonly GetCurrentLanguageTask $getCurrentLanguageTask,
    ) {
    }

    public function run(string|null $locale): Language
    {
        return $this->getCurrentLanguageTask->run($locale);
    }
}
