<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\AdminMenu\Actions\FindAdminMenuItemByIdAction;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\FindAdminMenuItemByIdRequest;
use App\Containers\AppSection\AdminMenu\UI\API\Transformers\AdminMenuItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class FindAdminMenuItemByIdController extends ApiController
{
    public function __invoke(FindAdminMenuItemByIdRequest $request, FindAdminMenuItemByIdAction $action): JsonResponse
    {
        $item = $action->run($request);

        $includeTranslations = str_contains((string) $request->query('include', ''), 'translations');

        return Response::create($item, new AdminMenuItemTransformer($includeTranslations))->ok();
    }
}
