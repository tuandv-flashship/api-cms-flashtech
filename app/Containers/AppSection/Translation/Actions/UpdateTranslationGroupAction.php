<?php

namespace App\Containers\AppSection\Translation\Actions;

use App\Containers\AppSection\Translation\Tasks\UpdateTranslationGroupTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateTranslationGroupAction extends ParentAction
{
    public function __construct(private readonly UpdateTranslationGroupTask $updateTranslationGroupTask)
    {
    }

    /**
     * @param array<string, mixed> $translations
     * @return array<string, string>
     */
    public function run(string $locale, string $group, array $translations): array
    {
        return $this->updateTranslationGroupTask->run($locale, $group, $translations);
    }
}
