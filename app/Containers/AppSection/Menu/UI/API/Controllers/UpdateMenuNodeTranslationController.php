<?php

namespace App\Containers\AppSection\Menu\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Menu\Actions\UpdateMenuNodeTranslationAction;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\UpdateMenuNodeTranslationRequest;
use App\Containers\AppSection\Menu\UI\API\Transformers\MenuNodeTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateMenuNodeTranslationController extends ApiController
{
    public function __invoke(UpdateMenuNodeTranslationRequest $request, UpdateMenuNodeTranslationAction $action): JsonResponse
    {
        $menuNode = $action->run($request);

        return Response::create($menuNode, MenuNodeTransformer::class)->ok();
    }
}
