<?php

namespace App\Containers\AppSection\Menu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Menu\Actions\GetMenuOptionsAction;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\GetMenuOptionsRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetMenuOptionsController extends ApiController
{
    public function __invoke(GetMenuOptionsRequest $request, GetMenuOptionsAction $action): JsonResponse
    {
        return Response::create()->ok([
            'data' => $action->run(),
        ]);
    }
}
