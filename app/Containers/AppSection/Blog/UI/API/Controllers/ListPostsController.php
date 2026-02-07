<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\ListPostsAction;
use App\Containers\AppSection\Blog\Supports\BlogOptions;
use App\Containers\AppSection\Blog\UI\API\Requests\ListPostsRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\PostTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListPostsController extends ApiController
{
    public function __invoke(ListPostsRequest $request, ListPostsAction $action): JsonResponse
    {
        $payload = $request->validated();
        $posts = $action->run($payload);

        $response = Response::create($posts, PostTransformer::class);

        if (BlogOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => BlogOptions::postOptions(),
            ]);
        }

        return $response->ok();
    }
}
