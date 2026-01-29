<?php

namespace App\Containers\AppSection\Tools\Actions;

use App\Containers\AppSection\Tools\Supports\Import\Importer;
use App\Containers\AppSection\Tools\Supports\Import\ValidationResult;
use App\Containers\AppSection\Tools\Tasks\ValidateDataSynchronizeImportTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ValidateDataSynchronizeImportAction extends ParentAction
{
    public function __construct(private readonly ValidateDataSynchronizeImportTask $task)
    {
    }

    public function run(Importer $importer, string $fileName, int $offset, int $limit, ?int $total = null): ValidationResult
    {
        return $this->task->run($importer, $fileName, $offset, $limit, $total);
    }
}
