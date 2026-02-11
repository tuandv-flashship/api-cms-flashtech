<?php

use App\Containers\AppSection\Menu\UI\API\Controllers\UpdateMenuNodeTranslationController;
use Illuminate\Support\Facades\Route;

$minLength = (int) config('hashids.connections.main.length', 16);

Route::patch('menu-nodes/{id}/translations', UpdateMenuNodeTranslationController::class)
    ->where('id', '[A-Za-z0-9]{' . $minLength . ',}')
    ->middleware(['auth:api']);
