<?php

namespace App\Containers\AppSection\Gallery\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Gallery\Actions\UpdateGalleryAction;
use App\Containers\AppSection\Gallery\UI\API\Requests\UpdateGalleryRequest;
use App\Containers\AppSection\Gallery\UI\API\Transformers\GalleryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class UpdateGalleryController extends ApiController
{
    public function __invoke(UpdateGalleryRequest $request, UpdateGalleryAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, [
            'name',
            'description',
            'status',
            'is_featured',
            'order',
            'image',
        ]);

        $gallery = $action->run(
            (int) $request->route('gallery_id'),
            $data,
            $payload['slug'] ?? null,
            $payload['gallery'] ?? null,
            $payload['seo_meta'] ?? null,
        );

        return Response::create($gallery, GalleryTransformer::class)->ok();
    }
}
