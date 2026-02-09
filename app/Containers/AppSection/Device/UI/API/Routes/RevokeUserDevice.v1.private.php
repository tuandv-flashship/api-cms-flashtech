<?php

/**
 * @apiGroup           Device
 *
 * @apiName            RevokeUserDevice
 *
 * @api                {delete} /v1/users/devices/:device_id Revoke Admin User Device
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

use App\Containers\AppSection\Device\UI\API\Controllers\RevokeUserDeviceController;
use Illuminate\Support\Facades\Route;

Route::delete('users/devices/{device_id}', RevokeUserDeviceController::class)
    ->name('api_user_revoke_device')
    ->middleware([
        'auth:api',
        'request.signature',
    ]);
