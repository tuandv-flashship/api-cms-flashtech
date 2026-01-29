<?php

namespace App\Containers\AppSection\Tools\Tasks;

use App\Containers\AppSection\Tools\Supports\Import\Importer;
use App\Containers\AppSection\Tools\Supports\Import\ImportResult;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class ImportDataSynchronizeTask extends ParentTask
{
    public function run(Importer $importer, string $fileName, int $offset, int $limit): ImportResult
    {
        return $importer->import($fileName, $offset, $limit);
    }
}
