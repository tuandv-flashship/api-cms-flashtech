<?php

namespace App\Containers\AppSection\Gallery\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Gallery\Actions\UpdateGalleryTranslationAction;
use App\Containers\AppSection\Gallery\UI\API\Requests\UpdateGalleryTranslationRequest;
use App\Containers\AppSection\Gallery\UI\API\Transformers\GalleryTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateGalleryTranslationController extends ApiController
{
    public function __invoke(UpdateGalleryTranslationRequest $request, UpdateGalleryTranslationAction $action): JsonResponse
    {
        $payload = $request->validated();

        $gallery = $action->run(
            (int) $request->route('gallery_id'),
            $payload,
            (string) $payload['lang_code'],
            $payload['slug'] ?? null,
            $payload['gallery'] ?? null,
            $payload['seo_meta'] ?? null,
        );

        return Response::create($gallery, GalleryTransformer::class)->ok();
    }
}
