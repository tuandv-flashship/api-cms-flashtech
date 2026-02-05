<?php

use App\Containers\AppSection\Member\UI\API\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('member/register', [RegisterController::class, 'registerMember'])
    ->name('api_member_register_member')
    ->middleware(['throttle:' . config('member.throttle.register', '6,1')]);
