<?php

use App\Containers\AppSection\Member\UI\API\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::post('members/reset-password', [ResetPasswordController::class, 'resetPassword'])
    ->name('api_member_reset_password')
    ->middleware(['throttle:' . config('member.throttle.password_reset', '6,1')]);
