<?php

namespace App\Containers\AppSection\Language\Actions;

use App\Containers\AppSection\AuditLog\Supports\AuditLogRecorder;
use App\Containers\AppSection\Language\Data\Repositories\LanguageRepository;
use App\Containers\AppSection\Language\Tasks\DeleteLanguageTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DeleteLanguageAction extends ParentAction
{
    public function __construct(
        private readonly DeleteLanguageTask $deleteLanguageTask,
        private readonly LanguageRepository $languageRepository,
    ) {
    }

    public function run(int $id): bool
    {
        $language = $this->languageRepository->findOrFail($id);
        $deleted = $this->deleteLanguageTask->run($id);

        if ($deleted) {
            AuditLogRecorder::recordModel('deleted', $language);
        }

        return $deleted;
    }
}
