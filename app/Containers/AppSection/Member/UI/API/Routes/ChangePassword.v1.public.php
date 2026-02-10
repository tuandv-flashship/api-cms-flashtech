<?php

use App\Containers\AppSection\Member\UI\API\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::post('member/password', [MemberController::class, 'changePassword'])
    ->name('api_member_change_password')
    ->middleware([
        'auth:member',
        'throttle:' . config('member.throttle.change_password', '10,1'),
    ]);
