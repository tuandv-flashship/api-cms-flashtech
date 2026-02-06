<?php

/**
 * @apiGroup           Device
 *
 * @apiName            ListMemberDevices
 *
 * @api                {get} /v1/member/devices List Member Devices
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated Member
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Device\UI\API\Controllers\ListMemberDevicesController;
use Illuminate\Support\Facades\Route;

Route::get('member/devices', ListMemberDevicesController::class)
    ->name('api_member_list_devices')
    ->middleware(['auth:member']);
