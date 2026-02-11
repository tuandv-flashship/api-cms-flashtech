<?php

namespace App\Containers\AppSection\Menu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Menu\Actions\GetMenuByLocationAction;
use App\Containers\AppSection\Menu\UI\API\Requests\GetMenuByLocationRequest;
use App\Containers\AppSection\Menu\UI\API\Transformers\MenuTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetMenuByLocationController extends ApiController
{
    public function __invoke(GetMenuByLocationRequest $request, GetMenuByLocationAction $action): JsonResponse
    {
        $menu = $action->run($request);

        return Response::create($menu, MenuTransformer::class)->ok();
    }
}
