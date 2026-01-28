<?php

namespace App\Containers\AppSection\Gallery\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Gallery\Actions\DeleteGalleryAction;
use App\Containers\AppSection\Gallery\UI\API\Requests\DeleteGalleryRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeleteGalleryController extends ApiController
{
    public function __invoke(DeleteGalleryRequest $request, DeleteGalleryAction $action): JsonResponse
    {
        $action->run((int) $request->route('gallery_id'));

        return Response::noContent();
    }
}
