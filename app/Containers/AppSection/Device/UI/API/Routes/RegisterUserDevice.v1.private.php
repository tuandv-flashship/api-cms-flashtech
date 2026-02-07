<?php

/**
 * @apiGroup           Device
 *
 * @apiName            RegisterUserDevice
 *
 * @api                {post} /v1/users/devices Register Admin User Device
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated User
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiBody            {String} device_id
 * @apiBody            {String} key_id
 * @apiBody            {String} public_key
 * @apiBody            {String} [platform]
 * @apiBody            {String} [device_name]
 * @apiBody            {String} [push_token]
 * @apiBody            {String} [push_provider]
 * @apiBody            {String} [app_version]
 */

use App\Containers\AppSection\Device\UI\API\Controllers\RegisterUserDeviceController;
use Illuminate\Support\Facades\Route;

Route::post('users/devices', RegisterUserDeviceController::class)
    ->name('api_user_register_device')
    ->middleware([
        'auth:api',
        'request.signature',
        'throttle:' . config('device.throttle.register', '20,1'),
    ]);
