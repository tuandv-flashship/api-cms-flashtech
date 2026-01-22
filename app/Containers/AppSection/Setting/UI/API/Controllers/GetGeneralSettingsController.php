<?php

namespace App\Containers\AppSection\Setting\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Setting\Actions\GetGeneralSettingsAction;
use App\Containers\AppSection\Setting\UI\API\Requests\GetGeneralSettingsRequest;
use App\Containers\AppSection\Setting\UI\API\Transformers\GeneralSettingsTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetGeneralSettingsController extends ApiController
{
    public function __invoke(GetGeneralSettingsRequest $request, GetGeneralSettingsAction $action): JsonResponse
    {
        $settings = (object) $action->run();

        return Response::create()
            ->item($settings, GeneralSettingsTransformer::class)
            ->ok();
    }
}
