<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\CreateTagAction;
use App\Containers\AppSection\Blog\UI\API\Requests\CreateTagRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\TagTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class CreateTagController extends ApiController
{
    public function __invoke(CreateTagRequest $request, CreateTagAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, [
            'name',
            'description',
            'status',
        ]);

        $tag = $action->run(
            $data,
            $payload['slug'] ?? null,
            $payload['meta'] ?? null,
        );

        return Response::create($tag, TagTransformer::class)->created();
    }
}
