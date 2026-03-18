<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\DownloadDataSynchronizeExampleAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\DownloadDataSynchronizeExampleRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadDataSynchronizeExampleController extends ApiController
{
    public function __invoke(
        DownloadDataSynchronizeExampleRequest $request,
        DataSynchronizeRegistry $registry,
        DownloadDataSynchronizeExampleAction $action,
        string $type,
    ): BinaryFileResponse {
        $importer = $registry->makeImporter($type);

        return $action->run($importer, $request->input('format'));
    }
}
