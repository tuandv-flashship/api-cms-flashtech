<?php

/**
 * @apiGroup           Device
 *
 * @apiName            RevokeUserDeviceKey
 *
 * @api                {delete} /v1/users/devices/:device_id/keys/:key_id Revoke Admin User Device Key
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated User
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} device_id
 * @apiParam           {String} key_id
 */

use App\Containers\AppSection\Device\UI\API\Controllers\RevokeUserDeviceKeyController;
use Illuminate\Support\Facades\Route;

Route::delete('users/devices/{device_id}/keys/{key_id}', RevokeUserDeviceKeyController::class)
    ->name('api_user_revoke_device_key')
    ->middleware(['auth:api']);
