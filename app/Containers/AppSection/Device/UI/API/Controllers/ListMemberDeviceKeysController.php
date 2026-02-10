<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\ListDeviceKeysAction;
use App\Containers\AppSection\Device\UI\API\Requests\ListMemberDeviceKeysRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceKeyTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class ListMemberDeviceKeysController extends ApiController
{
    public function __construct(
        private readonly ListDeviceKeysAction $listDeviceKeysAction,
    ) {
    }

    public function __invoke(ListMemberDeviceKeysRequest $request): JsonResponse
    {
        $memberId = (int) $request->user('member')->id;

        $keys = $this->listDeviceKeysAction->run(
            DeviceOwnerType::MEMBER,
            $memberId,
            (string) $request->route('device_id'),
        );

        return Response::create($keys, new DeviceKeyTransformer())->ok();
    }
}
