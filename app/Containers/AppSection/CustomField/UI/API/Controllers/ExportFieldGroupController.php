<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use App\Containers\AppSection\CustomField\Actions\ExportFieldGroupAction;
use App\Containers\AppSection\CustomField\UI\API\Requests\ExportFieldGroupRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ExportFieldGroupController extends ApiController
{
    public function __invoke(ExportFieldGroupRequest $request, ExportFieldGroupAction $action): JsonResponse
    {
        $data = $action->run((int) $request->field_group_id);

        return response()->json([
            'data' => $data,
        ]);
    }
}
