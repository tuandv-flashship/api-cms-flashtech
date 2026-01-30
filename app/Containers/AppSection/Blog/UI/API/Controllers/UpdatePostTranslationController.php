<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\UpdatePostTranslationAction;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdatePostTranslationRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\PostTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class UpdatePostTranslationController extends ApiController
{
    public function __invoke(UpdatePostTranslationRequest $request, UpdatePostTranslationAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, ['name', 'description', 'content']);

        $post = $action->run(
            $request->post_id,
            $data,
            (string) $payload['lang_code'],
            $payload['slug'] ?? null,
            $payload['seo_meta'] ?? null,
            $payload['gallery'] ?? null,
            $payload['custom_fields'] ?? null,
        );

        return Response::create($post, PostTransformer::class)->ok();
    }
}
