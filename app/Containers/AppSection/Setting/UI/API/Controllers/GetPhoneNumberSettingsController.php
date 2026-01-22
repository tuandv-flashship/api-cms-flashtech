<?php

namespace App\Containers\AppSection\Setting\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Setting\Actions\GetPhoneNumberSettingsAction;
use App\Containers\AppSection\Setting\UI\API\Requests\GetPhoneNumberSettingsRequest;
use App\Containers\AppSection\Setting\UI\API\Transformers\PhoneNumberSettingsTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetPhoneNumberSettingsController extends ApiController
{
    public function __invoke(GetPhoneNumberSettingsRequest $request, GetPhoneNumberSettingsAction $action): JsonResponse
    {
        $settings = (object) $action->run();

        return Response::create()
            ->item($settings, PhoneNumberSettingsTransformer::class)
            ->ok();
    }
}
