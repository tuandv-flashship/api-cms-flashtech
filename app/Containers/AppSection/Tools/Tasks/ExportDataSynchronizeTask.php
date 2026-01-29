<?php

namespace App\Containers\AppSection\Tools\Tasks;

use App\Containers\AppSection\Tools\Supports\DataSynchronizeStorage;
use App\Containers\AppSection\Tools\Supports\Export\Exporter;
use App\Containers\AppSection\Tools\Supports\SpreadsheetWriter;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportDataSynchronizeTask extends ParentTask
{
    public function __construct(
        private readonly SpreadsheetWriter $writer,
        private readonly DataSynchronizeStorage $storage
    ) {
    }

    public function run(Exporter $exporter): BinaryFileResponse
    {
        $relativePath = $this->storage->relativePath('exports/' . $exporter->fileName());
        $path = $this->storage->disk()->path($relativePath);

        $this->writer->write($exporter->getFormat(), $exporter->headers(), $exporter->rows(), $path);

        return response()
            ->download($path, $exporter->fileName())
            ->deleteFileAfterSend(true);
    }
}
