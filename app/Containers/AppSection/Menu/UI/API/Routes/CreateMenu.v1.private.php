<?php

use App\Containers\AppSection\Menu\UI\API\Controllers\CreateMenuController;
use Illuminate\Support\Facades\Route;

Route::post('menus', CreateMenuController::class)
    ->middleware(['auth:api']);
