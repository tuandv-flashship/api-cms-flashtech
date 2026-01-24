<?php

namespace App\Containers\AppSection\RequestLog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\RequestLog\Actions\GetRequestLogWidgetAction;
use App\Containers\AppSection\RequestLog\UI\API\Requests\GetRequestLogWidgetRequest;
use App\Containers\AppSection\RequestLog\UI\API\Transformers\RequestLogTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetRequestLogWidgetController extends ApiController
{
    public function __invoke(GetRequestLogWidgetRequest $request, GetRequestLogWidgetAction $action): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $limit = $request->input('limit');
        $perPage = is_numeric($limit)
            ? (int) $limit
            : (int) $request->input('paginate', 10);

        $logs = $action->run($page, $perPage);

        return Response::create($logs, RequestLogTransformer::class)->ok();
    }
}
