<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\CreateCategoryAction;
use App\Containers\AppSection\Blog\UI\API\Requests\CreateCategoryRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\CategoryTransformer;
use App\Containers\AppSection\Blog\UI\API\Transporters\CreateCategoryTransporter;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class CreateCategoryController extends ApiController
{
    public function __invoke(CreateCategoryRequest $request, CreateCategoryAction $action): JsonResponse
    {
        $transporter = CreateCategoryTransporter::fromRequest($request);

        $category = $action->run($transporter);

        return Response::create($category, CategoryTransformer::class)->created();
    }
}
