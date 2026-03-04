<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Controllers;

use App\Containers\AppSection\AdminMenu\Actions\ListAdminMenuItemsAction;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\ListAdminMenuItemsRequest;
use App\Containers\AppSection\AdminMenu\UI\API\Transformers\AdminMenuItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListAdminMenuItemsController extends ApiController
{
    public function __invoke(ListAdminMenuItemsRequest $request, ListAdminMenuItemsAction $action): JsonResponse
    {
        $tree = $action->run();

        return response()->json([
            'data' => array_map(
                fn (array $item): array => (new AdminMenuItemTransformer())->transform($item),
                $tree,
            ),
        ]);
    }
}
