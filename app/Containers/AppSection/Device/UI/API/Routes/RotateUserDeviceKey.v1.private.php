<?php

/**
 * @apiGroup           Device
 *
 * @apiName            RotateUserDeviceKey
 *
 * @api                {post} /v1/users/devices/:device_id/keys/rotate Rotate Admin User Device Key
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated User
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} device_id
 *
 * @apiBody            {String} key_id
 * @apiBody            {String} public_key
 */

use App\Containers\AppSection\Device\UI\API\Controllers\RotateUserDeviceKeyController;
use Illuminate\Support\Facades\Route;

Route::post('users/devices/{device_id}/keys/rotate', RotateUserDeviceKeyController::class)
    ->name('api_user_rotate_device_key')
    ->middleware([
        'auth:api',
        'request.signature',
        'throttle:' . config('device.throttle.rotate_key', '20,1'),
    ]);
