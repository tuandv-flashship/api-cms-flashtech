<?php

namespace App\Containers\AppSection\Menu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Menu\Actions\CreateMenuAction;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\CreateMenuRequest;
use App\Containers\AppSection\Menu\UI\API\Transformers\MenuTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class CreateMenuController extends ApiController
{
    public function __invoke(CreateMenuRequest $request, CreateMenuAction $action): JsonResponse
    {
        $menu = $action->run($request);

        return Response::create($menu, MenuTransformer::class)->created();
    }
}
