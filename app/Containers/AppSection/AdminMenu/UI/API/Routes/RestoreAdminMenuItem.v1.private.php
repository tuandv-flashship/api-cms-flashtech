<?php

use App\Containers\AppSection\AdminMenu\UI\API\Controllers\RestoreAdminMenuItemController;
use Illuminate\Support\Facades\Route;

Route::patch('admin-menus/{id}/restore', RestoreAdminMenuItemController::class)
    ->middleware(['auth:api']);
