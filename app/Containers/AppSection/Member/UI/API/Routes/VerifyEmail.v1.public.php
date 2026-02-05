<?php

use App\Containers\AppSection\Member\UI\API\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('member/email/verify/{id}/{hash}', [MemberController::class, 'verifyEmail'])
    ->name('api_member_verify_email');
