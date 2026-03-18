<?php

/**
 * @apiGroup           Tools
 * @apiName            ExportDataSynchronize
 * @api                {post} /v1/tools/data-synchronize/export/:type Export data by type
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ({type}.export)
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ExportDataSynchronizeController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/export/{type}', ExportDataSynchronizeController::class)
    ->middleware(['auth:api'])
    ->where('type', '[a-z\-]+');
