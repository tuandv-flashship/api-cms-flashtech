<?php

use App\Containers\AppSection\Menu\UI\API\Controllers\UpdateMenuController;
use Illuminate\Support\Facades\Route;

$minLength = (int) config('hashids.connections.main.length', 16);

Route::put('menus/{id}', UpdateMenuController::class)
    ->where('id', '[A-Za-z0-9]{' . $minLength . ',}')
    ->middleware(['auth:api']);
