<?php

use App\Containers\AppSection\AdminMenu\UI\API\Controllers\FindAdminMenuItemByIdController;
use Illuminate\Support\Facades\Route;

Route::get('admin-menus/{id}', FindAdminMenuItemByIdController::class)
    ->middleware(['auth:api']);
