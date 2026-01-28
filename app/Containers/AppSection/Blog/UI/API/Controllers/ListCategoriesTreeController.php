<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use App\Containers\AppSection\Blog\Actions\ListCategoriesTreeAction;
use App\Containers\AppSection\Blog\UI\API\Requests\ListCategoriesTreeRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListCategoriesTreeController extends ApiController
{
    public function __invoke(ListCategoriesTreeRequest $request, ListCategoriesTreeAction $action): JsonResponse
    {
        $payload = $request->validated();
        $status = isset($payload['status']) ? (string) $payload['status'] : null;

        $data = $action->run($status);

        return response()->json([
            'data' => $data,
        ]);
    }
}
