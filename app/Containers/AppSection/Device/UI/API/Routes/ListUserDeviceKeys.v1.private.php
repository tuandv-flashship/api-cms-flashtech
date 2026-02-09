<?php

/**
 * @apiGroup           Device
 *
 * @apiName            ListUserDeviceKeys
 *
 * @api                {get} /v1/users/devices/:device_id/keys List Admin User Device Keys
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated User
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} device_id
 */

use App\Containers\AppSection\Device\UI\API\Controllers\ListUserDeviceKeysController;
use Illuminate\Support\Facades\Route;

Route::get('users/devices/{device_id}/keys', ListUserDeviceKeysController::class)
    ->name('api_user_list_device_keys')
    ->middleware(['auth:api']);
