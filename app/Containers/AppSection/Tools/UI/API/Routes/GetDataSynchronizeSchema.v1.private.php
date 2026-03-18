<?php

/**
 * @apiGroup           Tools
 * @apiName            GetDataSynchronizeSchema
 * @api                {get} /v1/tools/data-synchronize/schema/:type Get data synchronize schema
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\GetDataSynchronizeSchemaController;
use Illuminate\Support\Facades\Route;

Route::get('tools/data-synchronize/schema/{type}', GetDataSynchronizeSchemaController::class)
    ->middleware(['auth:api'])
    ->where('type', '[a-z\-]+');
