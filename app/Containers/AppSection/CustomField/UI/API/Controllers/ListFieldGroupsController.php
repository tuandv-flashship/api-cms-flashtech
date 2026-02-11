<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\CustomField\Actions\ListFieldGroupsAction;
use App\Containers\AppSection\CustomField\UI\API\Requests\ListFieldGroupsRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\FieldGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListFieldGroupsController extends ApiController
{
    public function __invoke(ListFieldGroupsRequest $request, ListFieldGroupsAction $action): JsonResponse
    {
        $groups = $action->run();

        return Response::create($groups, FieldGroupTransformer::class)->ok();
    }
}

