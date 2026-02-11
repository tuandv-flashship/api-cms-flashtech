<?php

namespace App\Containers\AppSection\Menu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Menu\Actions\FindMenuByIdAction;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\FindMenuByIdRequest;
use App\Containers\AppSection\Menu\UI\API\Transformers\MenuTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class FindMenuByIdController extends ApiController
{
    public function __invoke(FindMenuByIdRequest $request, FindMenuByIdAction $action): JsonResponse
    {
        $menu = $action->run($request);

        return Response::create($menu, MenuTransformer::class)->ok();
    }
}
