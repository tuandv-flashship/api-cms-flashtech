<?php

use App\Containers\AppSection\Member\UI\API\Controllers\SocialLoginController;
use Illuminate\Support\Facades\Route;

Route::get('member/auth/{provider}/callback', [SocialLoginController::class, 'handleProviderCallback'])
    ->name('api_member_social_login_callback')
    ->middleware([
        'throttle:' . config('member.throttle.social_callback', '20,1'),
    ]);
