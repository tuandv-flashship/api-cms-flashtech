<?php

namespace App\Containers\AppSection\RequestLog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\RequestLog\Actions\DeleteRequestLogAction;
use App\Containers\AppSection\RequestLog\UI\API\Requests\DeleteRequestLogRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeleteRequestLogController extends ApiController
{
    public function __invoke(DeleteRequestLogRequest $request, DeleteRequestLogAction $action): JsonResponse
    {
        $id = (int) $request->route('request_log_id');
        $action->run($id);

        return Response::noContent();
    }
}
