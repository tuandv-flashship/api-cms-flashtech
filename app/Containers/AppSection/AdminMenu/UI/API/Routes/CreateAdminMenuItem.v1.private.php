<?php

use App\Containers\AppSection\AdminMenu\UI\API\Controllers\CreateAdminMenuItemController;
use Illuminate\Support\Facades\Route;

Route::post('admin-menus', CreateAdminMenuItemController::class)
    ->middleware(['auth:api']);
