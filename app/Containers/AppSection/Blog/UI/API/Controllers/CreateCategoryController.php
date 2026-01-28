<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\CreateCategoryAction;
use App\Containers\AppSection\Blog\UI\API\Requests\CreateCategoryRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\CategoryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class CreateCategoryController extends ApiController
{
    public function __invoke(CreateCategoryRequest $request, CreateCategoryAction $action): JsonResponse
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
            $data,
            $payload['slug'] ?? null,
            $payload['seo_meta'] ?? null,
        );

        return Response::create($category, CategoryTransformer::class)->created();
    }
}
