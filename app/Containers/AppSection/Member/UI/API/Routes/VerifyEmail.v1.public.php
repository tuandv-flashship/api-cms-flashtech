<?php

use App\Containers\AppSection\Member\UI\API\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('member/email/verify/{id}/{hash}', [MemberController::class, 'verifyEmail'])
    ->name('api_member_verify_email')
    ->middleware([
        'throttle:' . config('member.throttle.verify_email', '20,1'),
    ]);
