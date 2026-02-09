<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\UpdateCategoryAction;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateCategoryRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\CategoryTransformer;
use App\Containers\AppSection\Blog\UI\API\Transporters\UpdateCategoryTransporter;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateCategoryController extends ApiController
{
    public function __invoke(UpdateCategoryRequest $request, UpdateCategoryAction $action): JsonResponse
    {
        $transporter = UpdateCategoryTransporter::fromArray([
            'category_id' => $request->category_id,
            ...$request->validated(),
        ]);

        $category = $action->run($transporter);

        return Response::create($category, CategoryTransformer::class)->ok();
    }
}
