<?php

/**
 * @apiGroup           Device
 *
 * @apiName            RotateMemberDeviceKey
 *
 * @api                {post} /v1/member/devices/:device_id/keys/rotate Rotate Member Device Key
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated Member
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} device_id
 *
 * @apiBody            {String} key_id
 * @apiBody            {String} public_key
 */

use App\Containers\AppSection\Device\UI\API\Controllers\RotateMemberDeviceKeyController;
use Illuminate\Support\Facades\Route;

Route::post('member/devices/{device_id}/keys/rotate', RotateMemberDeviceKeyController::class)
    ->name('api_member_rotate_device_key')
    ->middleware([
        'auth:member',
        'request.signature',
        'throttle:' . config('device.throttle.rotate_key', '20,1'),
    ]);
