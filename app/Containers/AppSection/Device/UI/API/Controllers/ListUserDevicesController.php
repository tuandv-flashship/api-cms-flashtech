<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\ListDevicesAction;
use App\Containers\AppSection\Device\UI\API\Requests\ListUserDevicesRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class ListUserDevicesController extends ApiController
{
    public function __invoke(ListUserDevicesRequest $request): JsonResponse
    {
        $userId = (int) $request->user('api')->id;

        $devices = app(ListDevicesAction::class)->run(DeviceOwnerType::USER, $userId);

        return Response::create($devices, DeviceTransformer::class)->ok();
    }
}
