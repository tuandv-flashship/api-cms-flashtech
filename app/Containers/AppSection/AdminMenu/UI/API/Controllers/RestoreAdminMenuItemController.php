<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\AdminMenu\Actions\RestoreAdminMenuItemAction;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\RestoreAdminMenuItemRequest;
use App\Containers\AppSection\AdminMenu\UI\API\Transformers\AdminMenuItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class RestoreAdminMenuItemController extends ApiController
{
    public function __invoke(RestoreAdminMenuItemRequest $request, RestoreAdminMenuItemAction $action): JsonResponse
    {
        $item = $action->run($request);

        return Response::create($item, AdminMenuItemTransformer::class)->ok();
    }
}
