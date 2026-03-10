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

        $includeTranslations = str_contains((string) $request->query('include', ''), 'translations');
        $transformer = new AdminMenuItemTransformer($includeTranslations);

        return response()->json([
            'data' => array_map(
                fn (array $item): array => $transformer->transform($item),
                $tree,
            ),
        ]);
    }
}
