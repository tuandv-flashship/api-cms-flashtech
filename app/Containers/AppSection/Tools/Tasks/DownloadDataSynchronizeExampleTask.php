<?php

namespace App\Containers\AppSection\Tools\Tasks;

use App\Containers\AppSection\Tools\Supports\DataSynchronizeStorage;
use App\Containers\AppSection\Tools\Supports\Import\Importer;
use App\Containers\AppSection\Tools\Supports\SpreadsheetWriter;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadDataSynchronizeExampleTask extends ParentTask
{
    public function __construct(
        private readonly SpreadsheetWriter $writer,
        private readonly DataSynchronizeStorage $storage
    ) {
    }

    public function run(Importer $importer, string $format): BinaryFileResponse
    {
        $format = strtolower($format);
        $fileName = sprintf('example-%s.%s', str_replace(' ', '-', strtolower($importer->label())), $format);

        $relativePath = $this->storage->relativePath('examples/' . $fileName);
        $path = $this->storage->disk()->path($relativePath);

        $this->writer->write($format, $importer->exampleHeaders(), $importer->exampleRows(), $path);

        return response()
            ->download($path, $fileName)
            ->deleteFileAfterSend(true);
    }
}
