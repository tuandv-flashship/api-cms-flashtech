<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\ListCategoriesAction;
use App\Containers\AppSection\Blog\Supports\BlogOptions;
use App\Containers\AppSection\Blog\UI\API\Requests\ListCategoriesRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\CategoryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListCategoriesController extends ApiController
{
    public function __invoke(ListCategoriesRequest $request, ListCategoriesAction $action): JsonResponse
    {
        $categories = $action->run();

        $response = Response::create($categories, CategoryTransformer::class);

        if (BlogOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => BlogOptions::categoryOptions(),
            ]);
        }

        return $response->ok();
    }
}
