<?php

use App\Containers\AppSection\AdminMenu\UI\API\Controllers\BulkSaveAdminMenuItemsController;
use Illuminate\Support\Facades\Route;

Route::put('admin-menus/bulk', BulkSaveAdminMenuItemsController::class)
    ->middleware(['auth:api']);
