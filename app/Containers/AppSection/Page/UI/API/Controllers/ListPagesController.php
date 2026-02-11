<?php

namespace App\Containers\AppSection\Page\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Page\Actions\ListPagesAction;
use App\Containers\AppSection\Page\UI\API\Requests\ListPagesRequest;
use App\Containers\AppSection\Page\UI\API\Transformers\PageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListPagesController extends ApiController
{
    public function __invoke(ListPagesRequest $request, ListPagesAction $action): JsonResponse
    {
        $payload = $request->validated();
        $pages = $action->run($payload);

        return Response::create($pages, PageTransformer::class)->ok();
    }
}

