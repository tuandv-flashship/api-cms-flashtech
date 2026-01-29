<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\DownloadDataSynchronizeExampleAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\DownloadPostTranslationsExampleRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadPostTranslationsExampleController extends ApiController
{
    public function __invoke(
        DownloadPostTranslationsExampleRequest $request,
        DataSynchronizeRegistry $registry,
        DownloadDataSynchronizeExampleAction $action
    ): BinaryFileResponse {
        $importer = $registry->makeImporter('post-translations', $request->input('class'));

        return $action->run($importer, $request->input('format'));
    }
}
