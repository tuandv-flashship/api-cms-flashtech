<?php

namespace App\Containers\AppSection\Language\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Language\Models\Language;
use App\Containers\AppSection\Language\Tasks\CreateLanguageTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class CreateLanguageAction extends ParentAction
{
    public function __construct(
        private readonly CreateLanguageTask $createLanguageTask,
    ) {
    }

    public function run(array $data): Language
    {
        $language = $this->createLanguageTask->run($data);

        AuditLogRecorder::recordModel('created', $language);

        return $language;
    }
}
