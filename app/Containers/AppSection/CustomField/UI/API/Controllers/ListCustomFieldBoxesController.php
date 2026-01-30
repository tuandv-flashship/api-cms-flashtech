<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\CustomField\Actions\ListCustomFieldBoxesAction;
use App\Containers\AppSection\CustomField\UI\API\Requests\ListCustomFieldBoxesRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\CustomFieldBoxTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListCustomFieldBoxesController extends ApiController
{
    public function __invoke(ListCustomFieldBoxesRequest $request, ListCustomFieldBoxesAction $action): JsonResponse
    {
        $payload = $request->validated();

        $boxes = $action->run(
            (string) $payload['model'],
            isset($payload['reference_id']) ? (int) $payload['reference_id'] : null,
            $payload['rules'] ?? []
        );

        return Response::create($boxes, CustomFieldBoxTransformer::class)->ok();
    }
}
