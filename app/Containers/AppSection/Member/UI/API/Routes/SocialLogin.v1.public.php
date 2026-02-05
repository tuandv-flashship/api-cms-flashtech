<?php

use App\Containers\AppSection\Member\UI\API\Controllers\SocialLoginController;
use Illuminate\Support\Facades\Route;

/**
 * Header: x-client=mobile (optional). If "mobile", callback returns refresh_token in JSON.
 */
Route::get('member/auth/{provider}', [SocialLoginController::class, 'redirectToProvider'])
    ->name('api_member_social_login_redirect');

Route::get('member/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])
    ->name('api_member_social_login_callback');
