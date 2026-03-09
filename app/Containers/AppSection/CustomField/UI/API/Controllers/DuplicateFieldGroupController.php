<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use App\Containers\AppSection\CustomField\Actions\DuplicateFieldGroupAction;
use App\Containers\AppSection\CustomField\UI\API\Requests\DuplicateFieldGroupRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\FieldGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DuplicateFieldGroupController extends ApiController
{
    public function __invoke(DuplicateFieldGroupRequest $request, DuplicateFieldGroupAction $action): JsonResponse
    {
        $group = $action->run((int) $request->field_group_id);

        return $this->created($this->transform($group, FieldGroupTransformer::class));
    }
}
