<?php

/**
 * @apiGroup           Device
 *
 * @apiName            ListMemberDeviceKeys
 *
 * @api                {get} /v1/member/devices/:device_id/keys List Member Device Keys
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated Member
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} device_id
 */

use App\Containers\AppSection\Device\UI\API\Controllers\ListMemberDeviceKeysController;
use Illuminate\Support\Facades\Route;

Route::get('member/devices/{device_id}/keys', ListMemberDeviceKeysController::class)
    ->name('api_member_list_device_keys')
    ->middleware(['auth:member']);
