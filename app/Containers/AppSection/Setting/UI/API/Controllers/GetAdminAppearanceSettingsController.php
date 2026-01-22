<?php

namespace App\Containers\AppSection\Setting\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Setting\Actions\GetAdminAppearanceSettingsAction;
use App\Containers\AppSection\Setting\UI\API\Requests\GetAdminAppearanceSettingsRequest;
use App\Containers\AppSection\Setting\UI\API\Transformers\AdminAppearanceSettingsTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetAdminAppearanceSettingsController extends ApiController
{
    public function __invoke(GetAdminAppearanceSettingsRequest $request, GetAdminAppearanceSettingsAction $action): JsonResponse
    {
        $settings = (object) $action->run();

        return Response::create()
            ->item($settings, AdminAppearanceSettingsTransformer::class)
            ->ok();
    }
}
