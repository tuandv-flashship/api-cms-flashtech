<?php

namespace App\Containers\AppSection\Translation\Actions;

use App\Containers\AppSection\Translation\Tasks\ListTranslationLocalesTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ListTranslationLocalesAction extends ParentAction
{
    public function __construct(private readonly ListTranslationLocalesTask $listTranslationLocalesTask)
    {
    }

    /**
     * @return array{installed: array<int, array<string, mixed>>, available: array<int, array<string, mixed>>}
     */
    public function run(): array
    {
        return $this->listTranslationLocalesTask->run();
    }
}
