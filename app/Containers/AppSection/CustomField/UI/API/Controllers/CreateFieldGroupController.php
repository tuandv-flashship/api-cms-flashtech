<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\CustomField\Actions\CreateFieldGroupAction;
use App\Containers\AppSection\CustomField\Supports\CustomFieldOptions;
use App\Containers\AppSection\CustomField\UI\API\Requests\CreateFieldGroupRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\FieldGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class CreateFieldGroupController extends ApiController
{
    public function __invoke(CreateFieldGroupRequest $request, CreateFieldGroupAction $action): JsonResponse
    {
        $group = $action->run($request->validated());

        $response = Response::create($group, FieldGroupTransformer::class)
            ->parseIncludes(['items']);

        if (CustomFieldOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => CustomFieldOptions::options(),
            ]);
        }

        return $response->created();
    }
}
