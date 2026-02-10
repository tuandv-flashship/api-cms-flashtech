<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\RevokeDeviceAction;
use App\Containers\AppSection\Device\UI\API\Requests\RevokeUserDeviceRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class RevokeUserDeviceController extends ApiController
{
    public function __construct(
        private readonly RevokeDeviceAction $revokeDeviceAction,
    ) {
    }

    public function __invoke(RevokeUserDeviceRequest $request): JsonResponse
    {
        $userId = (int) $request->user('api')->id;

        $device = $this->revokeDeviceAction->run(
            DeviceOwnerType::USER,
            $userId,
            (string) $request->route('device_id'),
        );

        return Response::create($device, DeviceTransformer::class)->ok();
    }
}
