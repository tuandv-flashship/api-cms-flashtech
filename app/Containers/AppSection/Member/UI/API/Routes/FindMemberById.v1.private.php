<?php

use App\Containers\AppSection\Member\UI\API\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('members/{id}', [AdminController::class, 'findMemberById'])
    ->name('api_member_find_member_by_id')
    ->middleware(['auth:api']);
