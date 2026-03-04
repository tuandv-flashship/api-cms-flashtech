<?php

use App\Containers\AppSection\AdminMenu\UI\API\Controllers\DeleteAdminMenuItemController;
use Illuminate\Support\Facades\Route;

Route::delete('admin-menus/{id}', DeleteAdminMenuItemController::class)
    ->middleware(['auth:api']);
