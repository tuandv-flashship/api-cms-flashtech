<?php

namespace App\Containers\AppSection\Translation\Actions;

use App\Containers\AppSection\Translation\Tasks\GetTranslationGroupTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class GetTranslationGroupAction extends ParentAction
{
    public function __construct(private readonly GetTranslationGroupTask $getTranslationGroupTask)
    {
    }

    /**
     * @return array<string, string>
     */
    public function run(string $locale, string $group): array
    {
        return $this->getTranslationGroupTask->run($locale, $group);
    }
}
