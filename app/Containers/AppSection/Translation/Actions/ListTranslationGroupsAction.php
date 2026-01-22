<?php

namespace App\Containers\AppSection\Translation\Actions;

use App\Containers\AppSection\Translation\Tasks\ListTranslationGroupsTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ListTranslationGroupsAction extends ParentAction
{
    public function __construct(private readonly ListTranslationGroupsTask $listTranslationGroupsTask)
    {
    }

    /**
     * @return array<int, string>
     */
    public function run(string $locale): array
    {
        return $this->listTranslationGroupsTask->run($locale);
    }
}
