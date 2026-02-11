<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Supports\BlogOptions;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetPostOptionsController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        return Response::create()->ok([
            'data' => BlogOptions::postOptions(),
        ]);
    }
}

