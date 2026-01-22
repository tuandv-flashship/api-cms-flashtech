<?php

namespace App\Containers\AppSection\Translation\Actions;

use App\Containers\AppSection\Translation\Tasks\CreateTranslationLocaleTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreateTranslationLocaleAction extends ParentAction
{
    public function __construct(private readonly CreateTranslationLocaleTask $createTranslationLocaleTask)
    {
    }

    /**
     * @return array{locale: string, downloaded: bool, copied: bool}
     */
    public function run(string $locale, string $source = 'github', bool $includeVendor = true): array
    {
        return $this->createTranslationLocaleTask->run($locale, $source, $includeVendor);
    }
}
