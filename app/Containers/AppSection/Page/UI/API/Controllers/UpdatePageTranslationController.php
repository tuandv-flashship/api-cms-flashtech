<?php

namespace App\Containers\AppSection\Page\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Page\Actions\UpdatePageTranslationAction;
use App\Containers\AppSection\Page\UI\API\Requests\UpdatePageTranslationRequest;
use App\Containers\AppSection\Page\UI\API\Transformers\PageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class UpdatePageTranslationController extends ApiController
{
    public function __invoke(UpdatePageTranslationRequest $request, UpdatePageTranslationAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, ['name', 'description', 'content']);

        $page = $action->run(
            $request->page_id,
            $data,
            (string) $payload['lang_code'],
            $payload['slug'] ?? null,
            $payload['seo_meta'] ?? null,
            $payload['custom_fields'] ?? null,
        );

        return Response::create($page, PageTransformer::class)->ok();
    }
}
