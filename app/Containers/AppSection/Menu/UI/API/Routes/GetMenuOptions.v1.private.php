<?php

use App\Containers\AppSection\Menu\UI\API\Controllers\GetMenuOptionsController;
use Illuminate\Support\Facades\Route;

Route::get('menus/options', GetMenuOptionsController::class)
    ->middleware(['auth:api']);
