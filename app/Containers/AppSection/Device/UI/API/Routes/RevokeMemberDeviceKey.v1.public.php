<?php

/**
 * @apiGroup           Device
 *
 * @apiName            RevokeMemberDeviceKey
 *
 * @api                {delete} /v1/member/devices/:device_id/keys/:key_id Revoke Member Device Key
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated Member
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} device_id
 * @apiParam           {String} key_id
 */

use App\Containers\AppSection\Device\UI\API\Controllers\RevokeMemberDeviceKeyController;
use Illuminate\Support\Facades\Route;

Route::delete('member/devices/{device_id}/keys/{key_id}', RevokeMemberDeviceKeyController::class)
    ->name('api_member_revoke_device_key')
    ->middleware(['auth:member']);
