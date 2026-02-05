<?php

use App\Containers\AppSection\Member\UI\API\Controllers\MemberController;
use Illuminate\Support\Facades\Route;

Route::put('member/profile', [MemberController::class, 'updateProfile'])
    ->name('api_member_update_profile')
    ->middleware(['auth:member']);
