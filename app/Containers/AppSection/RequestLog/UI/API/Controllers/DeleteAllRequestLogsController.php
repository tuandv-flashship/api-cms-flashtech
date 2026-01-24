<?php

namespace App\Containers\AppSection\RequestLog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\RequestLog\Actions\DeleteAllRequestLogsAction;
use App\Containers\AppSection\RequestLog\UI\API\Requests\DeleteAllRequestLogsRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeleteAllRequestLogsController extends ApiController
{
    public function __invoke(DeleteAllRequestLogsRequest $request, DeleteAllRequestLogsAction $action): JsonResponse
    {
        $action->run();

        return Response::noContent();
    }
}
