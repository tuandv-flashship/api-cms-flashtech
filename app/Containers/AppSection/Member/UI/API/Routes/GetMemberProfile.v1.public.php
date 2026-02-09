<?php

use App\Containers\AppSection\Member\UI\API\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::get('member/profile', [MemberController::class, 'getProfile'])
    ->name('api_member_get_profile')
    ->middleware(['auth:member']);
