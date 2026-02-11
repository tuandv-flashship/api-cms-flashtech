<?php

use App\Containers\AppSection\Menu\UI\API\Controllers\ListMenusController;
use Illuminate\Support\Facades\Route;

Route::get('menus', ListMenusController::class)
    ->middleware(['auth:api']);
