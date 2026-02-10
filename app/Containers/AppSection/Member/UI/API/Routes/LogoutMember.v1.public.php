<?php

/**
 * @apiGroup           Member
 *
 * @apiName            LogoutMember
 *
 * @api                {post} /v1/member/logout Member logout
 *
 * @apiDescription     Revoke current access token.
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated Member
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiHeader          {String} x-client=mobile Optional.
 */

use App\Containers\AppSection\Member\UI\API\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::post('member/logout', LogoutController::class)
    ->name('api_member_logout')
    ->middleware([
        'auth:member',
        'throttle:' . config('member.throttle.logout', '20,1'),
    ]);
