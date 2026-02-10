<?php

use App\Containers\AppSection\Member\UI\API\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('members', [AdminController::class, 'getAllMembers'])
    ->name('api_member_get_all_members')
    ->middleware(['auth:api']);
