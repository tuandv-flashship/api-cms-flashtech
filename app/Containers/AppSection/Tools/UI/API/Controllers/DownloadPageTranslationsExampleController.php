<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\DownloadDataSynchronizeExampleAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\DownloadPageTranslationsExampleRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadPageTranslationsExampleController extends ApiController
{
    public function __invoke(
        DownloadPageTranslationsExampleRequest $request,
        DataSynchronizeRegistry $registry,
        DownloadDataSynchronizeExampleAction $action
    ): BinaryFileResponse {
        $importer = $registry->makeImporter('page-translations');

        return $action->run($importer, $request->input('format'));
    }
}
