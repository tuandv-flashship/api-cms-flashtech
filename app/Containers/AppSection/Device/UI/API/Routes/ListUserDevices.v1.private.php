<?php

/**
 * @apiGroup           Device
 *
 * @apiName            ListUserDevices
 *
 * @api                {get} /v1/users/devices List Admin User Devices
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated User
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Device\UI\API\Controllers\ListUserDevicesController;
use Illuminate\Support\Facades\Route;

Route::get('users/devices', ListUserDevicesController::class)
    ->name('api_user_list_devices')
    ->middleware(['auth:api']);
