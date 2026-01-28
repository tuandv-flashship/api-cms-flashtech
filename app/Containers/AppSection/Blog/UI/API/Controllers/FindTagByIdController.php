<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\FindTagByIdAction;
use App\Containers\AppSection\Blog\Supports\BlogOptions;
use App\Containers\AppSection\Blog\UI\API\Requests\FindTagByIdRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\TagTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class FindTagByIdController extends ApiController
{
    public function __invoke(FindTagByIdRequest $request, FindTagByIdAction $action): JsonResponse
    {
        $tag = $action->run($request->tag_id);

        $response = Response::create($tag, TagTransformer::class);

        if (BlogOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => BlogOptions::tagOptions(),
            ]);
        }

        return $response->ok();
    }
}
