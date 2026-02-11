<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\FindPostByIdAction;
use App\Containers\AppSection\Blog\UI\API\Requests\FindPostByIdRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\PostTransformer;
use App\Ship\Supports\RequestIncludes;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class FindPostByIdController extends ApiController
{
    public function __invoke(FindPostByIdRequest $request, FindPostByIdAction $action): JsonResponse
    {
        $post = $action->run(
            $request->post_id,
            RequestIncludes::has($request->query('include'), 'author'),
        );

        return Response::create($post, PostTransformer::class)->ok();
    }
}

