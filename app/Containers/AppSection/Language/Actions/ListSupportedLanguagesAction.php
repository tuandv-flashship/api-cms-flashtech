<?php

namespace App\Containers\AppSection\Language\Actions;

use App\Containers\AppSection\Language\Tasks\ListSupportedLanguagesTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ListSupportedLanguagesAction extends ParentAction
{
    public function __construct(
        private readonly ListSupportedLanguagesTask $listSupportedLanguagesTask,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(): array
    {
        return $this->listSupportedLanguagesTask->run();
    }
}
