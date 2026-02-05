<?php

use App\Containers\AppSection\Member\UI\API\Controllers\ForgotPasswordController;
use Illuminate\Support\Facades\Route;

Route::post('members/forgot-password', [ForgotPasswordController::class, 'forgotPassword'])
    ->name('api_member_forgot_password')
    ->middleware(['throttle:' . config('member.throttle.password_reset', '6,1')]);
