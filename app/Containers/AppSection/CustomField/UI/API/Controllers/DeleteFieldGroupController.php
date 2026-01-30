<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\CustomField\Actions\DeleteFieldGroupAction;
use App\Containers\AppSection\CustomField\UI\API\Requests\DeleteFieldGroupRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeleteFieldGroupController extends ApiController
{
    public function __invoke(DeleteFieldGroupRequest $request, DeleteFieldGroupAction $action): JsonResponse
    {
        $action->run($request->field_group_id);

        return Response::noContent();
    }
}
