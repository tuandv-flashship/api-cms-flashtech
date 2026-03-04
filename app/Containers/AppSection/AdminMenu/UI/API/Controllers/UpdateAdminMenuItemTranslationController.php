<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\AdminMenu\Actions\UpdateAdminMenuItemTranslationAction;
use App\Containers\AppSection\AdminMenu\UI\API\Requests\UpdateAdminMenuItemTranslationRequest;
use App\Containers\AppSection\AdminMenu\UI\API\Transformers\AdminMenuItemTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateAdminMenuItemTranslationController extends ApiController
{
    public function __invoke(UpdateAdminMenuItemTranslationRequest $request, UpdateAdminMenuItemTranslationAction $action): JsonResponse
    {
        $item = $action->run($request);

        return Response::create($item, AdminMenuItemTransformer::class)->ok();
    }
}
