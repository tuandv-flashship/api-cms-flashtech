<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Controllers;

use App\Containers\AppSection\AdminMenu\Actions\BulkSaveAdminMenuItemsAction;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\BulkSaveAdminMenuItemsRequest;
use App\Containers\AppSection\AdminMenu\UI\API\Transformers\AdminMenuItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class BulkSaveAdminMenuItemsController extends ApiController
{
    public function __invoke(BulkSaveAdminMenuItemsRequest $request, BulkSaveAdminMenuItemsAction $action): JsonResponse
    {
        $tree = $action->run($request);

        return response()->json([
            'data' => array_map(
                fn (array $item): array => (new AdminMenuItemTransformer())->transform($item),
                $tree,
            ),
        ]);
    }
}
