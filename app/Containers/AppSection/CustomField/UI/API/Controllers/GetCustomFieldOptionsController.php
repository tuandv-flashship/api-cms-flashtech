<?php

namespace App\Containers\AppSection\CustomField\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\CustomField\Supports\CustomFieldOptions;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetCustomFieldOptionsController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        return Response::create()->ok([
            'data' => CustomFieldOptions::options(),
        ]);
    }
}

