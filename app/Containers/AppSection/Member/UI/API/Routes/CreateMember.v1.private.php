<?php

use App\Containers\AppSection\Member\UI\API\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::post('members', [AdminController::class, 'createMember'])
    ->name('api_member_create_member')
    ->middleware(['auth:api']);

