<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\DownloadDataSynchronizeExampleAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\DownloadOtherTranslationsExampleRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadOtherTranslationsExampleController extends ApiController
{
    public function __invoke(
        DownloadOtherTranslationsExampleRequest $request,
        DataSynchronizeRegistry $registry,
        DownloadDataSynchronizeExampleAction $action
    ): BinaryFileResponse {
        $importer = $registry->makeImporter('other-translations');

        return $action->run($importer, $request->input('format'));
    }
}
