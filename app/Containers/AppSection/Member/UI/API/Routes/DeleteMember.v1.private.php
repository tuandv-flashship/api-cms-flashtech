<?php

use App\Containers\AppSection\Member\UI\API\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::delete('members/{id}', [AdminController::class, 'deleteMember'])
    ->name('api_member_delete_member')
    ->middleware(['auth:api']);

