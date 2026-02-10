<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\CustomField\Actions\ListFieldGroupsAction;
use App\Containers\AppSection\CustomField\Supports\CustomFieldOptions;
use App\Containers\AppSection\CustomField\UI\API\Requests\ListFieldGroupsRequest;
use App\Containers\AppSection\CustomField\UI\API\Transformers\FieldGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListFieldGroupsController extends ApiController
{
    public function __invoke(ListFieldGroupsRequest $request, ListFieldGroupsAction $action): JsonResponse
    {
        $groups = $action->run();

        $response = Response::create($groups, FieldGroupTransformer::class);

        if (CustomFieldOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => CustomFieldOptions::options(),
            ]);
        }

        return $response->ok();
    }
}
