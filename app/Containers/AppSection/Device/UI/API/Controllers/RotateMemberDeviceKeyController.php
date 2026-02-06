<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\RotateDeviceKeyAction;
use App\Containers\AppSection\Device\UI\API\Requests\RotateMemberDeviceKeyRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceKeyTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class RotateMemberDeviceKeyController extends ApiController
{
    public function __invoke(RotateMemberDeviceKeyRequest $request): JsonResponse
    {
        $memberId = (int) $request->user('member')->id;

        $key = app(RotateDeviceKeyAction::class)->run(
            DeviceOwnerType::MEMBER,
            $memberId,
            (string) $request->route('device_id'),
            (string) $request->input('key_id'),
            (string) $request->input('public_key'),
        );

        return Response::create($key, DeviceKeyTransformer::class)
            ->addMeta(['device_id' => (string) $request->route('device_id')])
            ->ok();
    }
}
