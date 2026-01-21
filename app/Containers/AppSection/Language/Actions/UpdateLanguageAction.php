<?php

namespace App\Containers\AppSection\Language\Actions;

use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Language\Tasks\UpdateLanguageTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class UpdateLanguageAction extends ParentAction
{
    public function __construct(
        private readonly UpdateLanguageTask $updateLanguageTask,
    ) {
    }

    public function run(int $id, array $data): Language
    {
        return $this->updateLanguageTask->run($id, $data);
    }
}
