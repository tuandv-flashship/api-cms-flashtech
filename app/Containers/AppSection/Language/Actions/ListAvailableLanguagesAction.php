<?php

namespace App\Containers\AppSection\Language\Actions;

use App\Containers\AppSection\Language\Tasks\ListAvailableLanguagesTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ListAvailableLanguagesAction extends ParentAction
{
    public function __construct(
        private readonly ListAvailableLanguagesTask $listAvailableLanguagesTask,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(): array
    {
        return $this->listAvailableLanguagesTask->run();
    }
}
