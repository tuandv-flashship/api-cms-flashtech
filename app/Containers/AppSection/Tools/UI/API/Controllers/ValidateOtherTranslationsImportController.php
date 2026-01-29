<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\ValidateDataSynchronizeImportAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\ValidateOtherTranslationsImportRequest;
use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Tools\UI\API\Transformers\DataSynchronizeValidationTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ValidateOtherTranslationsImportController extends ApiController
{
    public function __invoke(
        ValidateOtherTranslationsImportRequest $request,
        DataSynchronizeRegistry $registry,
        ValidateDataSynchronizeImportAction $action
    ): JsonResponse {
        $importer = $registry->makeImporter('other-translations');

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
