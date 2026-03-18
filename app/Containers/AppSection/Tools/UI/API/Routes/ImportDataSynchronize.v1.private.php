<?php

/**
 * @apiGroup           Tools
 * @apiName            ImportDataSynchronize
 * @api                {post} /v1/tools/data-synchronize/import/:type Import data by type
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ({type}.import)
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ImportDataSynchronizeController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/{type}', ImportDataSynchronizeController::class)
    ->middleware(['auth:api'])
    ->where('type', '[a-z\-]+');
