<?php

namespace App\Containers\AppSection\Menu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Menu\Actions\ListMenusAction;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\ListMenusRequest;
use App\Containers\AppSection\Menu\UI\API\Transformers\MenuTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListMenusController extends ApiController
{
    public function __invoke(ListMenusRequest $request, ListMenusAction $action): JsonResponse
    {
        $menus = $action->run($request);

        return Response::create($menus, MenuTransformer::class)->ok();
    }
}
