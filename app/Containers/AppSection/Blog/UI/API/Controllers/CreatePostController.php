<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\CreatePostAction;
use App\Containers\AppSection\Blog\UI\API\Requests\CreatePostRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\PostTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class CreatePostController extends ApiController
{
    public function __invoke(CreatePostRequest $request, CreatePostAction $action): JsonResponse
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
            $data,
            $payload['category_ids'] ?? null,
            $payload['tag_ids'] ?? null,
            $payload['tag_names'] ?? null,
            $payload['slug'] ?? null,
            $payload['gallery'] ?? null,
            $payload['seo_meta'] ?? null,
            $payload['custom_fields'] ?? null,
        );

        return Response::create($post, PostTransformer::class)->created();
    }
}
