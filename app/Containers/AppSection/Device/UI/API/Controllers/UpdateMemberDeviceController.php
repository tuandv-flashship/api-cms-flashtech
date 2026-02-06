<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\UpdateDeviceAction;
use App\Containers\AppSection\Device\UI\API\Requests\UpdateMemberDeviceRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class UpdateMemberDeviceController extends ApiController
{
    public function __invoke(UpdateMemberDeviceRequest $request): JsonResponse
    {
        $memberId = (int) $request->user('member')->id;

        $device = app(UpdateDeviceAction::class)->run(
            DeviceOwnerType::MEMBER,
            $memberId,
            (string) $request->route('device_id'),
            $request->validated(),
        );

        return Response::create($device, DeviceTransformer::class)->ok();
    }
}
