<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\UpdateCategoryAction;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateCategoryRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\CategoryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class UpdateCategoryController extends ApiController
{
    public function __invoke(UpdateCategoryRequest $request, UpdateCategoryAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, [
            'name',
            'description',
            'status',
            'parent_id',
            'icon',
            'order',
            'is_featured',
            'is_default',
        ]);

        $category = $action->run(
            $request->category_id,
            $data,
            $payload['slug'] ?? null,
            $payload['seo_meta'] ?? null,
            $payload['custom_fields'] ?? null,
        );

        return Response::create($category, CategoryTransformer::class)->ok();
    }
}
