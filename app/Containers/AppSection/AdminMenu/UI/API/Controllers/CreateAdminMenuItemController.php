<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\AdminMenu\Actions\CreateAdminMenuItemAction;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\CreateAdminMenuItemRequest;
use App\Containers\AppSection\AdminMenu\UI\API\Transformers\AdminMenuItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class CreateAdminMenuItemController extends ApiController
{
    public function __invoke(CreateAdminMenuItemRequest $request, CreateAdminMenuItemAction $action): JsonResponse
    {
        $item = $action->run($request);

        return Response::create($item, AdminMenuItemTransformer::class)->created();
    }
}
