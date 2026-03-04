<?php

use App\Containers\AppSection\AdminMenu\UI\API\Controllers\UpdateAdminMenuItemTranslationController;
use Illuminate\Support\Facades\Route;

Route::patch('admin-menus/{id}/translations', UpdateAdminMenuItemTranslationController::class)
    ->middleware(['auth:api']);
