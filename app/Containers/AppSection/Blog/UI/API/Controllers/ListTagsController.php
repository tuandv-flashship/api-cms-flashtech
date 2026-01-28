<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\ListTagsAction;
use App\Containers\AppSection\Blog\UI\API\Requests\ListTagsRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\TagTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListTagsController extends ApiController
{
    public function __invoke(ListTagsRequest $request, ListTagsAction $action): JsonResponse
    {
        $payload = $request->validated();
        $perPage = (int) ($payload['limit'] ?? $payload['per_page'] ?? 15);
        $page = (int) ($payload['page'] ?? 1);

        $tags = $action->run($payload, $perPage, $page);

        return Response::create($tags, TagTransformer::class)->ok();
    }
}
