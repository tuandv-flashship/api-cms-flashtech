<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\DownloadDataSynchronizeExampleAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\DownloadPostsExampleRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadPostsExampleController extends ApiController
{
    public function __invoke(
        DownloadPostsExampleRequest $request,
        DataSynchronizeRegistry $registry,
        DownloadDataSynchronizeExampleAction $action
    ): BinaryFileResponse {
        $importer = $registry->makeImporter('posts');

        return $action->run($importer, $request->input('format'));
    }
}
