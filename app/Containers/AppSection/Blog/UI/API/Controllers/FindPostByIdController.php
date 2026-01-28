<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\FindPostByIdAction;
use App\Containers\AppSection\Blog\Supports\BlogOptions;
use App\Containers\AppSection\Blog\UI\API\Requests\FindPostByIdRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\PostTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class FindPostByIdController extends ApiController
{
    public function __invoke(FindPostByIdRequest $request, FindPostByIdAction $action): JsonResponse
    {
        $post = $action->run($request->post_id);

        $response = Response::create($post, PostTransformer::class);

        if (BlogOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => BlogOptions::postOptions(),
            ]);
        }

        return $response->ok();
    }
}
