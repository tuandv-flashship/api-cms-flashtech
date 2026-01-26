<?php

namespace App\Containers\AppSection\AuditLog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\AuditLog\Actions\GetAuditLogWidgetAction;
use App\Containers\AppSection\AuditLog\UI\API\Requests\GetAuditLogWidgetRequest;
use App\Containers\AppSection\AuditLog\UI\API\Transformers\AuditLogTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetAuditLogWidgetController extends ApiController
{
    public function __invoke(GetAuditLogWidgetRequest $request, GetAuditLogWidgetAction $action): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $limit = $request->input('limit');
        $perPage = is_numeric($limit)
            ? (int) $limit
            : (int) $request->input('paginate', 10);

        $logs = $action->run($page, $perPage);

        return Response::create($logs, AuditLogTransformer::class)->ok();
    }
}
