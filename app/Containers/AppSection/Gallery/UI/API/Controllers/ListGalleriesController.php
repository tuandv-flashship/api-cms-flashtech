<?php

namespace App\Containers\AppSection\Gallery\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Gallery\Actions\ListGalleriesAction;
use App\Containers\AppSection\Gallery\Supports\GalleryOptions;
use App\Containers\AppSection\Gallery\UI\API\Requests\ListGalleriesRequest;
use App\Containers\AppSection\Gallery\UI\API\Transformers\GalleryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListGalleriesController extends ApiController
{
    public function __invoke(ListGalleriesRequest $request, ListGalleriesAction $action): JsonResponse
    {
        $galleries = $action->run();

        $response = Response::create($galleries, GalleryTransformer::class);

        if (GalleryOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => GalleryOptions::galleryOptions(),
            ]);
        }

        return $response->ok();
    }
}
