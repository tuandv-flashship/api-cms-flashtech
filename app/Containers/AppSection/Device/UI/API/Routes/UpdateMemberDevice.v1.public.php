<?php

/**
 * @apiGroup           Device
 *
 * @apiName            UpdateMemberDevice
 *
 * @api                {patch} /v1/member/devices/:device_id Update Member Device
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
 * @apiBody            {String} [platform]
 * @apiBody            {String} [device_name]
 * @apiBody            {String} [push_token]
 * @apiBody            {String} [push_provider]
 * @apiBody            {String} [app_version]
 */

use App\Containers\AppSection\Device\UI\API\Controllers\UpdateMemberDeviceController;
use Illuminate\Support\Facades\Route;

Route::patch('member/devices/{device_id}', UpdateMemberDeviceController::class)
    ->name('api_member_update_device')
    ->middleware([
        'auth:member',
        'request.signature',
        'throttle:' . config('device.throttle.update', '30,1'),
    ]);
