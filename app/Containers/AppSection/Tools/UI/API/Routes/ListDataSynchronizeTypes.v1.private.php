<?php

/**
 * @apiGroup           Tools
 * @apiName            ListDataSynchronizeTypes
 * @api                {get} /v1/tools/data-synchronize/types List available data synchronize types
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ListDataSynchronizeTypesController;
use Illuminate\Support\Facades\Route;

Route::get('tools/data-synchronize/types', ListDataSynchronizeTypesController::class)
    ->middleware(['auth:api']);
