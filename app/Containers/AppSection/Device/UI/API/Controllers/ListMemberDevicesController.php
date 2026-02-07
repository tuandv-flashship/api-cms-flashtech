<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\ListDevicesAction;
use App\Containers\AppSection\Device\UI\API\Requests\ListMemberDevicesRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class ListMemberDevicesController extends ApiController
{
    public function __construct(
        private readonly ListDevicesAction $listDevicesAction,
    ) {
    }

    public function __invoke(ListMemberDevicesRequest $request): JsonResponse
    {
        $memberId = (int) $request->user('member')->id;

        $devices = $this->listDevicesAction->run(DeviceOwnerType::MEMBER, $memberId);

        return Response::create($devices, DeviceTransformer::class)->ok();
    }
}
