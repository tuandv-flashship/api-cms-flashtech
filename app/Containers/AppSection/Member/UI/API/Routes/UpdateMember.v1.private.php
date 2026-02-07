<?php

use App\Containers\AppSection\Member\UI\API\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::put('members/{id}', [AdminController::class, 'updateMember'])
    ->name('api_member_update_member')
    ->middleware(['auth:api']);

