<?php

use App\Containers\AppSection\Menu\UI\API\Controllers\FindMenuByIdController;
use Illuminate\Support\Facades\Route;

$minLength = (int) config('hashids.connections.main.length', 16);

Route::get('menus/{id}', FindMenuByIdController::class)
    ->where('id', '[A-Za-z0-9]{' . $minLength . ',}')
    ->middleware(['auth:api']);
