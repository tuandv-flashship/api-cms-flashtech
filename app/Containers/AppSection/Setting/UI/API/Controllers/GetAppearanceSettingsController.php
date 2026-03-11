<?php

namespace App\Containers\AppSection\Setting\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Containers\AppSection\Setting\Actions\GetAdminAppearanceSettingsAction;
use App\Containers\AppSection\Setting\UI\API\Requests\GetAppearanceSettingsRequest;
use App\Containers\AppSection\Setting\UI\API\Transformers\AppearanceSettingsTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetAppearanceSettingsController extends ApiController
{
    public function __invoke(
        GetAppearanceSettingsRequest $request,
        GetAdminAppearanceSettingsAction $action,
        MediaService $mediaService,
    ): JsonResponse {
        $settings = (object) $action->run();
        $transformer = new AppearanceSettingsTransformer($mediaService);

        return Response::create()
            ->item($settings, $transformer)
            ->ok();
    }
}
