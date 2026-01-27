<?php

namespace App\Containers\AppSection\AuditLog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\AuditLog\Actions\ListAuditLogsAction;
use App\Containers\AppSection\AuditLog\UI\API\Requests\ListAuditLogsRequest;
use App\Containers\AppSection\AuditLog\UI\API\Transformers\AuditLogTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListAuditLogsController extends ApiController
{
    public function __invoke(ListAuditLogsRequest $request, ListAuditLogsAction $action): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $limit = $request->input('limit');
        $perPage = is_numeric($limit)
            ? (int) $limit
            : (int) $request->input('per_page', $request->input('paginate', 15));

        $logs = $action->run($page, $perPage);

        return Response::create($logs, AuditLogTransformer::class)->ok();
    }
}
