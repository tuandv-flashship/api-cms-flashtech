<?php

namespace App\Containers\AppSection\Gallery\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Gallery\Supports\GalleryOptions;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetGalleryOptionsController extends ApiController
{
    public function __invoke(): JsonResponse
    {
        return Response::create()->ok([
            'data' => GalleryOptions::galleryOptions(),
        ]);
    }
}

