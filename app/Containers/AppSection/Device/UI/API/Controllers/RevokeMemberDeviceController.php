<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\RevokeDeviceAction;
use App\Containers\AppSection\Device\UI\API\Requests\RevokeMemberDeviceRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class RevokeMemberDeviceController extends ApiController
{
    public function __invoke(RevokeMemberDeviceRequest $request): JsonResponse
    {
        $memberId = (int) $request->user('member')->id;

        $device = app(RevokeDeviceAction::class)->run(
            DeviceOwnerType::MEMBER,
            $memberId,
            (string) $request->route('device_id'),
        );

        return Response::create($device, DeviceTransformer::class)->ok();
    }
}
