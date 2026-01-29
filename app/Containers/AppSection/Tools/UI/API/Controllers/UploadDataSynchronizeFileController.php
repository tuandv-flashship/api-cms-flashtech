<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Tools\Actions\UploadDataSynchronizeFileAction;
use App\Containers\AppSection\Tools\UI\API\Requests\UploadDataSynchronizeFileRequest;
use App\Containers\AppSection\Tools\UI\API\Transformers\DataSynchronizeUploadTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UploadDataSynchronizeFileController extends ApiController
{
    public function __invoke(UploadDataSynchronizeFileRequest $request, UploadDataSynchronizeFileAction $action): JsonResponse
    {
        $result = $action->run($request->file('file'));

        return Response::create()
            ->item((object) $result, DataSynchronizeUploadTransformer::class)
            ->ok();
    }
}
