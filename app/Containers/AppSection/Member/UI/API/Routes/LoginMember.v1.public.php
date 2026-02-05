<?php

use App\Containers\AppSection\Member\UI\API\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

/**
 * Header: x-client=mobile (optional). If "mobile", refresh_token is returned in JSON.
 */
Route::post('member/login', [LoginController::class, 'loginMember'])
    ->name('api_member_login_member')
    ->middleware(['throttle:' . config('member.throttle.login', '6,1')]);
