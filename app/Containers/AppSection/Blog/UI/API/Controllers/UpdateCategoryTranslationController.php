<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\UpdateCategoryTranslationAction;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateCategoryTranslationRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\CategoryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class UpdateCategoryTranslationController extends ApiController
{
    public function __invoke(UpdateCategoryTranslationRequest $request, UpdateCategoryTranslationAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, ['name', 'description']);

        $category = $action->run(
            $request->category_id,
            $data,
            (string) $payload['lang_code'],
            $payload['slug'] ?? null,
            $payload['seo_meta'] ?? null,
        );

        return Response::create($category, CategoryTransformer::class)->ok();
    }
}
