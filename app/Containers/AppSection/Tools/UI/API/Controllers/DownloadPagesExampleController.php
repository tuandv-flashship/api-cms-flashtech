<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\DownloadDataSynchronizeExampleAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\DownloadPagesExampleRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadPagesExampleController extends ApiController
{
    public function __invoke(
        DownloadPagesExampleRequest $request,
        DataSynchronizeRegistry $registry,
        DownloadDataSynchronizeExampleAction $action
    ): BinaryFileResponse {
        $importer = $registry->makeImporter('pages');

        return $action->run($importer, $request->input('format'));
    }
}
