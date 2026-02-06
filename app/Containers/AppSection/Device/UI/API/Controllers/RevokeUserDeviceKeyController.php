<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\RevokeDeviceKeyAction;
use App\Containers\AppSection\Device\UI\API\Requests\RevokeUserDeviceKeyRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceKeyTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class RevokeUserDeviceKeyController extends ApiController
{
    public function __invoke(RevokeUserDeviceKeyRequest $request): JsonResponse
    {
        $userId = (int) $request->user('api')->id;

        $deviceKey = app(RevokeDeviceKeyAction::class)->run(
            DeviceOwnerType::USER,
            $userId,
            (string) $request->route('device_id'),
            (string) $request->route('key_id'),
        );

        return Response::create($deviceKey, DeviceKeyTransformer::class)->ok();
    }
}
