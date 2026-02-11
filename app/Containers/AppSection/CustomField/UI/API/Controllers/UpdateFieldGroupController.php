<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\CustomField\Actions\UpdateFieldGroupAction;
use App\Containers\AppSection\CustomField\UI\API\Requests\UpdateFieldGroupRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\FieldGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateFieldGroupController extends ApiController
{
    public function __invoke(UpdateFieldGroupRequest $request, UpdateFieldGroupAction $action): JsonResponse
    {
        $group = $action->run($request->field_group_id, $request->validated());

        return Response::create($group, FieldGroupTransformer::class)
            ->parseIncludes(['items'])
            ->ok();
    }
}

