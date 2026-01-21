<?php

namespace App\Containers\AppSection\Language\Actions;

use App\Containers\AppSection\Language\Tasks\DeleteLanguageTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeleteLanguageAction extends ParentAction
{
    public function __construct(
        private readonly DeleteLanguageTask $deleteLanguageTask,
    ) {
    }

    public function run(int $id): bool
    {
        return $this->deleteLanguageTask->run($id);
    }
}
