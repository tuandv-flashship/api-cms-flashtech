<?php

namespace App\Containers\AppSection\Device\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Device\Actions\RegisterDeviceAction;
use App\Containers\AppSection\Device\UI\API\Requests\RegisterMemberDeviceRequest;
use App\Containers\AppSection\Device\UI\API\Transformers\DeviceTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use App\Containers\AppSection\Device\Enums\DeviceOwnerType;

final class RegisterMemberDeviceController extends ApiController
{
    public function __invoke(RegisterMemberDeviceRequest $request): JsonResponse
    {
        $memberId = (int) $request->user('member')->id;

        $result = app(RegisterDeviceAction::class)->run(
            $request->validated(),
            DeviceOwnerType::MEMBER,
            $memberId,
        );

        return Response::create($result['device'], DeviceTransformer::class)
            ->addMeta(['key_id' => $result['key']->key_id])
            ->ok();
    }
}
