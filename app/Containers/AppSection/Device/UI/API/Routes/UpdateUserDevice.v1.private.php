<?php

/**
 * @apiGroup           Device
 *
 * @apiName            UpdateUserDevice
 *
 * @api                {patch} /v1/users/devices/:device_id Update Admin User Device
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
 * @apiBody            {String} [platform]
 * @apiBody            {String} [device_name]
 * @apiBody            {String} [push_token]
 * @apiBody            {String} [push_provider]
 * @apiBody            {String} [app_version]
 */

use App\Containers\AppSection\Device\UI\API\Controllers\UpdateUserDeviceController;
use Illuminate\Support\Facades\Route;

Route::patch('users/devices/{device_id}', UpdateUserDeviceController::class)
    ->name('api_user_update_device')
    ->middleware([
        'auth:api',
        'request.signature',
    ]);
