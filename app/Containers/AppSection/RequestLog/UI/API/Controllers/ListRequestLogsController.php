<?php

namespace App\Containers\AppSection\RequestLog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\RequestLog\Actions\ListRequestLogsAction;
use App\Containers\AppSection\RequestLog\UI\API\Requests\ListRequestLogsRequest;
use App\Containers\AppSection\RequestLog\UI\API\Transformers\RequestLogTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListRequestLogsController extends ApiController
{
    public function __invoke(ListRequestLogsRequest $request, ListRequestLogsAction $action): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $limit = $request->input('limit');
        $perPage = is_numeric($limit)
            ? (int) $limit
            : (int) $request->input('per_page', $request->input('paginate', 15));

        $logs = $action->run($page, $perPage);

        return Response::create($logs, RequestLogTransformer::class)->ok();
    }
}
