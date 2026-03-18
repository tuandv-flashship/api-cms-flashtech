<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Tools\Actions\ImportDataSynchronizeAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\ImportPageTranslationsRequest;
use App\Containers\AppSection\Tools\UI\API\Transformers\DataSynchronizeImportTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ImportPageTranslationsController extends ApiController
{
    public function __invoke(
        ImportPageTranslationsRequest $request,
        DataSynchronizeRegistry $registry,
        ImportDataSynchronizeAction $action
    ): JsonResponse {
        $importer = $registry->makeImporter('page-translations');

        $result = $action->run(
            $importer,
            $request->input('file_name'),
            $request->integer('offset'),
            $request->integer('limit')
        );

        return Response::create()
            ->item($result, DataSynchronizeImportTransformer::class)
            ->ok();
    }
}
