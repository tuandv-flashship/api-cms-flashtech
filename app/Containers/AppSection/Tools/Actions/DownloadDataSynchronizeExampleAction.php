<?php

namespace App\Containers\AppSection\Tools\Actions;

use App\Containers\AppSection\Tools\Supports\Import\Importer;
use App\Containers\AppSection\Tools\Tasks\DownloadDataSynchronizeExampleTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadDataSynchronizeExampleAction extends ParentAction
{
    public function __construct(private readonly DownloadDataSynchronizeExampleTask $task)
    {
    }

    public function run(Importer $importer, string $format): BinaryFileResponse
    {
        return $this->task->run($importer, $format);
    }
}
