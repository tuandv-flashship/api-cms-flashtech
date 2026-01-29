<?php

namespace App\Containers\AppSection\Tools\Actions;

use App\Containers\AppSection\Tools\Supports\Export\Exporter;
use App\Containers\AppSection\Tools\Tasks\ExportDataSynchronizeTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportDataSynchronizeAction extends ParentAction
{
    public function __construct(private readonly ExportDataSynchronizeTask $task)
    {
    }

    public function run(Exporter $exporter): BinaryFileResponse
    {
        return $this->task->run($exporter);
    }
}
