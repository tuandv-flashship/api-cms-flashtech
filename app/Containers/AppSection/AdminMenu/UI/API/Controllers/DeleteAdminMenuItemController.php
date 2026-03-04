<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Controllers;

use App\Containers\AppSection\AdminMenu\Actions\DeleteAdminMenuItemAction;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\DeleteAdminMenuItemRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeleteAdminMenuItemController extends ApiController
{
    public function __invoke(DeleteAdminMenuItemRequest $request, DeleteAdminMenuItemAction $action): JsonResponse
    {
        $action->run($request);

        return response()->json(null, 204);
    }
}
