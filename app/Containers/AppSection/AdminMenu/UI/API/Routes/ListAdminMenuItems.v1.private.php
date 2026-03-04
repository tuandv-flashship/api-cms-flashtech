<?php

use App\Containers\AppSection\AdminMenu\UI\API\Controllers\ListAdminMenuItemsController;
use Illuminate\Support\Facades\Route;

Route::get('admin-menus', ListAdminMenuItemsController::class)
    ->middleware(['auth:api']);
