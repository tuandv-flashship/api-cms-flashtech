<?php

namespace App\Containers\AppSection\Language\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Language\Tasks\SetDefaultLanguageTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class SetDefaultLanguageAction extends ParentAction
{
    public function __construct(
        private readonly SetDefaultLanguageTask $setDefaultLanguageTask,
    ) {
    }

    public function run(int $id): Language
    {
        $language = $this->setDefaultLanguageTask->run($id);

        AuditLogRecorder::recordModel('updated', $language);

        return $language;
    }
}
