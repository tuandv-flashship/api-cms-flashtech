<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\AdminMenu\Actions\UpdateAdminMenuItemAction;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\UpdateAdminMenuItemRequest;
use App\Containers\AppSection\AdminMenu\UI\API\Transformers\AdminMenuItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateAdminMenuItemController extends ApiController
{
    public function __invoke(UpdateAdminMenuItemRequest $request, UpdateAdminMenuItemAction $action): JsonResponse
    {
        $item = $action->run($request);

        return Response::create($item, AdminMenuItemTransformer::class)->ok();
    }
}
