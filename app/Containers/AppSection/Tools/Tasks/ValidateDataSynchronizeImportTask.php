<?php

namespace App\Containers\AppSection\Tools\Tasks;

use App\Containers\AppSection\Tools\Supports\Import\Importer;
use App\Containers\AppSection\Tools\Supports\Import\ValidationResult;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ValidateDataSynchronizeImportTask extends ParentTask
{
    public function run(Importer $importer, string $fileName, int $offset, int $limit, ?int $total = null): ValidationResult
    {
        return $importer->validate($fileName, $offset, $limit, $total);
    }
}
