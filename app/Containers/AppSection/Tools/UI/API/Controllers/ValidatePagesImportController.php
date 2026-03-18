<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\ValidateDataSynchronizeImportAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\ValidatePagesImportRequest;
use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Tools\UI\API\Transformers\DataSynchronizeValidationTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ValidatePagesImportController extends ApiController
{
    public function __invoke(
        ValidatePagesImportRequest $request,
        DataSynchronizeRegistry $registry,
        ValidateDataSynchronizeImportAction $action
    ): JsonResponse {
        $importer = $registry->makeImporter('pages');

        $result = $action->run(
            $importer,
            $request->input('file_name'),
            $request->integer('offset'),
            $request->integer('limit'),
            $request->input('total')
        );

        return Response::create()
            ->item($result, DataSynchronizeValidationTransformer::class)
            ->ok();
    }
}
