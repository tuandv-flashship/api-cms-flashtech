<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\ListTagsAction;
use App\Containers\AppSection\Blog\Supports\BlogOptions;
use App\Containers\AppSection\Blog\UI\API\Requests\ListTagsRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\TagTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListTagsController extends ApiController
{
    public function __invoke(ListTagsRequest $request, ListTagsAction $action): JsonResponse
    {
        $payload = $request->validated();
        $tags = $action->run($payload);

        $response = Response::create($tags, TagTransformer::class);

        if (BlogOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => BlogOptions::tagOptions(),
            ]);
        }

        return $response->ok();
    }
}
