<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\UpdatePostAction;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdatePostRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\PostTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class UpdatePostController extends ApiController
{
    public function __invoke(UpdatePostRequest $request, UpdatePostAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, [
            'name',
            'description',
            'content',
            'status',
            'is_featured',
            'image',
            'format_type',
        ]);

        $post = $action->run(
            $request->post_id,
            $data,
            $payload['category_ids'] ?? null,
            $payload['tag_ids'] ?? null,
            $payload['tag_names'] ?? null,
            $payload['slug'] ?? null,
            $payload['meta'] ?? null,
        );

        return Response::create($post, PostTransformer::class)->ok();
    }
}
