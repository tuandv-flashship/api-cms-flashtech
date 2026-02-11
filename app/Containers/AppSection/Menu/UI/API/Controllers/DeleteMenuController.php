<?php

namespace App\Containers\AppSection\Menu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Menu\Actions\DeleteMenuAction;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\DeleteMenuRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeleteMenuController extends ApiController
{
    public function __invoke(DeleteMenuRequest $request, DeleteMenuAction $action): JsonResponse
    {
        $action->run($request);

        return Response::noContent();
    }
}
