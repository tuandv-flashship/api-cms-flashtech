<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use App\Containers\AppSection\CustomField\Actions\ImportFieldGroupAction;
use App\Containers\AppSection\CustomField\UI\API\Requests\ImportFieldGroupRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\FieldGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ImportFieldGroupController extends ApiController
{
    public function __invoke(ImportFieldGroupRequest $request, ImportFieldGroupAction $action): JsonResponse
    {
        $payload = $request->validated();
        $group = $action->run($payload['data']);

        return $this->created($this->transform($group, FieldGroupTransformer::class));
    }
}
