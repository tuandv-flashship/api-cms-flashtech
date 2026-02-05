<?php

/**
 * @apiGroup           Member
 *
 * @apiName            RefreshMemberToken
 *
 * @api                {post} /v1/member/token/refresh Refresh member token
 *
 * @apiDescription     Get new access token using a refresh token.
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      none
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} x-client=mobile Optional. If "mobile", refresh_token will be returned in JSON.
 * @apiHeader          {String} x-csrf-token Required for web clients when CSRF is enabled.
 *
 * @apiBody            {String} refresh_token
 */

use App\Containers\AppSection\Member\UI\API\Controllers\RefreshTokenController;
use Illuminate\Support\Facades\Route;

Route::post('member/token/refresh', RefreshTokenController::class)
    ->name('api_member_refresh_token')
    ->middleware(['member.csrf', 'throttle:' . config('member.throttle.refresh', '12,1')]);
