<?php

use App\Containers\AppSection\Member\UI\API\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('members', [AdminController::class, 'getAllMembers'])
    ->name('api_member_get_all_members')
    ->middleware(['auth:api']);

Route::post('members', [AdminController::class, 'createMember'])
    ->name('api_member_create_member')
    ->middleware(['auth:api']);

Route::get('members/{id}', [AdminController::class, 'findMemberById'])
    ->name('api_member_find_member_by_id')
    ->middleware(['auth:api']);

Route::put('members/{id}', [AdminController::class, 'updateMember'])
    ->name('api_member_update_member')
    ->middleware(['auth:api']);

Route::delete('members/{id}', [AdminController::class, 'deleteMember'])
    ->name('api_member_delete_member')
    ->middleware(['auth:api']);
