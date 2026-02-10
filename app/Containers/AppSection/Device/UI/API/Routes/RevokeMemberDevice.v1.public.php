<?php

/**
 * @apiGroup           Device
 *
 * @apiName            RevokeMemberDevice
 *
 * @api                {delete} /v1/member/devices/:device_id Revoke Member Device
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated Member
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} device_id
 */

use App\Containers\AppSection\Device\UI\API\Controllers\RevokeMemberDeviceController;
use Illuminate\Support\Facades\Route;

Route::delete('member/devices/{device_id}', RevokeMemberDeviceController::class)
    ->name('api_member_revoke_device')
    ->middleware([
        'auth:member',
        'request.signature',
        'throttle:' . config('device.throttle.revoke_device', '20,1'),
    ]);
