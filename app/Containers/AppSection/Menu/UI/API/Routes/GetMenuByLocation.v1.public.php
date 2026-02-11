<?php

use App\Containers\AppSection\Menu\UI\API\Controllers\GetMenuByLocationController;
use Illuminate\Support\Facades\Route;

Route::get('menus/location/{location}', GetMenuByLocationController::class)
    ->where('location', '[A-Za-z0-9\-_]+');
