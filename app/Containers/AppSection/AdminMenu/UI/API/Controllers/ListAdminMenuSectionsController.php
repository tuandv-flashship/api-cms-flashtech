<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Controllers;

use App\Containers\AppSection\AdminMenu\Actions\ListAdminMenuSectionsAction;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\ListAdminMenuSectionsRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListAdminMenuSectionsController extends ApiController
{
    public function __invoke(ListAdminMenuSectionsRequest $request, ListAdminMenuSectionsAction $action): JsonResponse
    {
        return response()->json(['data' => $action->run()]);
    }
}
