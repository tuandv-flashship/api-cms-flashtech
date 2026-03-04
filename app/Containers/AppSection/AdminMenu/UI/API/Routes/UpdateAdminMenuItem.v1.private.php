<?php

use App\Containers\AppSection\AdminMenu\UI\API\Controllers\UpdateAdminMenuItemController;
use Illuminate\Support\Facades\Route;

Route::put('admin-menus/{id}', UpdateAdminMenuItemController::class)
    ->middleware(['auth:api']);
