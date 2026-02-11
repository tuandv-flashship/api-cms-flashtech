<?php

namespace App\Containers\AppSection\Page\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Page\Supports\PageOptions;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetPageOptionsController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        return Response::create()->ok([
            'data' => PageOptions::pageOptions(),
        ]);
    }
}

