<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\UpdateTagTranslationAction;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateTagTranslationRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\TagTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class UpdateTagTranslationController extends ApiController
{
    public function __invoke(UpdateTagTranslationRequest $request, UpdateTagTranslationAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, ['name', 'description']);

        $tag = $action->run(
            $request->tag_id,
            $data,
            (string) $payload['lang_code'],
            $payload['slug'] ?? null,
            $payload['seo_meta'] ?? null,
        );

        return Response::create($tag, TagTransformer::class)->ok();
    }
}
