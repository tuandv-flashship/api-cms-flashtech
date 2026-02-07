<?php

/**
 * @apiGroup           Device
 *
 * @apiName            RegisterMemberDevice
 *
 * @api                {post} /v1/member/devices Register Member Device
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated Member
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

use App\Containers\AppSection\Device\UI\API\Controllers\RegisterMemberDeviceController;
use Illuminate\Support\Facades\Route;

Route::post('member/devices', RegisterMemberDeviceController::class)
    ->name('api_member_register_device')
    ->middleware([
        'auth:member',
        'request.signature',
        'throttle:' . config('device.throttle.register', '20,1'),
    ]);
