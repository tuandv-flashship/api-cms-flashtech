<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\UpdateTagAction;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateTagRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\TagTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class UpdateTagController extends ApiController
{
    public function __invoke(UpdateTagRequest $request, UpdateTagAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, [
            'name',
            'description',
            'status',
        ]);

        $tag = $action->run(
            $request->tag_id,
            $data,
            $payload['slug'] ?? null,
            $payload['meta'] ?? null,
        );

        return Response::create($tag, TagTransformer::class)->ok();
    }
}
