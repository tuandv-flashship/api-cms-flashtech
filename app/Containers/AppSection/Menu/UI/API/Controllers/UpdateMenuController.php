<?php

namespace App\Containers\AppSection\Menu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Menu\Actions\UpdateMenuAction;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\UpdateMenuRequest;
use App\Containers\AppSection\Menu\UI\API\Transformers\MenuTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateMenuController extends ApiController
{
    public function __invoke(UpdateMenuRequest $request, UpdateMenuAction $action): JsonResponse
    {
        $menu = $action->run($request);

        return Response::create($menu, MenuTransformer::class)->ok();
    }
}
