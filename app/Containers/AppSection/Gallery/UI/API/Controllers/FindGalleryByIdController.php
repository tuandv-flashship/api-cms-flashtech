<?php

namespace App\Containers\AppSection\Gallery\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Gallery\Actions\FindGalleryByIdAction;
use App\Containers\AppSection\Gallery\Supports\GalleryOptions;
use App\Containers\AppSection\Gallery\UI\API\Requests\FindGalleryByIdRequest;
use App\Containers\AppSection\Gallery\UI\API\Transformers\GalleryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class FindGalleryByIdController extends ApiController
{
    public function __invoke(FindGalleryByIdRequest $request, FindGalleryByIdAction $action): JsonResponse
    {
        $gallery = $action->run((int) $request->route('gallery_id'));

        $response = Response::create($gallery, GalleryTransformer::class);

        if (GalleryOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => GalleryOptions::galleryOptions(),
            ]);
        }

        return $response->ok();
    }
}
