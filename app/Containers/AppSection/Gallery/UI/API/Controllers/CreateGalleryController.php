<?php

namespace App\Containers\AppSection\Gallery\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Gallery\Actions\CreateGalleryAction;
use App\Containers\AppSection\Gallery\UI\API\Requests\CreateGalleryRequest;
use App\Containers\AppSection\Gallery\UI\API\Transformers\GalleryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class CreateGalleryController extends ApiController
{
    public function __invoke(CreateGalleryRequest $request, CreateGalleryAction $action): JsonResponse
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
            $data,
            $payload['slug'] ?? null,
            $payload['gallery'] ?? null,
            $payload['seo_meta'] ?? null,
        );

        return Response::create($gallery, GalleryTransformer::class)->created();
    }
}
