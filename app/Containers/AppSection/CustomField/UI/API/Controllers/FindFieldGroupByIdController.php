<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\CustomField\Actions\FindFieldGroupByIdAction;
use App\Containers\AppSection\CustomField\UI\API\Requests\FindFieldGroupByIdRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\FieldGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class FindFieldGroupByIdController extends ApiController
{
    public function __invoke(FindFieldGroupByIdRequest $request, FindFieldGroupByIdAction $action): JsonResponse
    {
        $group = $action->run($request->field_group_id);

        return Response::create($group, FieldGroupTransformer::class)
            ->parseIncludes(['items'])
            ->ok();
    }
}

