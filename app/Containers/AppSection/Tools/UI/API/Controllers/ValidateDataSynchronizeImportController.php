<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Tools\Actions\ValidateDataSynchronizeImportAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\ImportDataSynchronizeRequest;
use App\Containers\AppSection\Tools\UI\API\Transformers\DataSynchronizeValidationTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ValidateDataSynchronizeImportController extends ApiController
{
    public function __invoke(
        ImportDataSynchronizeRequest $request,
        DataSynchronizeRegistry $registry,
        ValidateDataSynchronizeImportAction $action,
        string $type,
    ): JsonResponse {
        $importer = $registry->makeImporter($type);

        $result = $action->run(
            $importer,
            $request->input('file_name'),
            $request->integer('offset'),
            $request->integer('limit'),
            $request->input('total')
        );

        $from = max(1, $result->offset - $result->count + 1);
        $to = $result->offset;

        $message = __('data-synchronize.validate_progress', [
            'from' => $from,
            'to' => $to,
        ]);

        $response = Response::create()
            ->item($result, DataSynchronizeValidationTransformer::class)
            ->ok();

        $data = $response->getData(true);
        $data['message'] = $message;
        $response->setData($data);

        return $response;
    }
}
