<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\ListDeviceKeysAction;
use App\Containers\AppSection\Device\UI\API\Requests\ListUserDeviceKeysRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceKeyTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class ListUserDeviceKeysController extends ApiController
{
    public function __construct(
        private readonly ListDeviceKeysAction $listDeviceKeysAction,
    ) {
    }

    public function __invoke(ListUserDeviceKeysRequest $request): JsonResponse
    {
        $userId = (int) $request->user('api')->id;
        $includePublicKey = filter_var($request->query('include_public_key'), FILTER_VALIDATE_BOOL);

        $keys = $this->listDeviceKeysAction->run(
            DeviceOwnerType::USER,
            $userId,
            (string) $request->route('device_id'),
        );

        return Response::create($keys, new DeviceKeyTransformer($includePublicKey))->ok();
    }
}
