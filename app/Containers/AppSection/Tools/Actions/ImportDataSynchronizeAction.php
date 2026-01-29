<?php

namespace App\Containers\AppSection\Tools\Actions;

use App\Containers\AppSection\Tools\Supports\Import\Importer;
use App\Containers\AppSection\Tools\Supports\Import\ImportResult;
use App\Containers\AppSection\Tools\Tasks\ImportDataSynchronizeTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class ImportDataSynchronizeAction extends ParentAction
{
    public function __construct(private readonly ImportDataSynchronizeTask $task)
    {
    }

    public function run(Importer $importer, string $fileName, int $offset, int $limit): ImportResult
    {
        return $this->task->run($importer, $fileName, $offset, $limit);
    }
}
