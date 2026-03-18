<?php

/**
 * @apiGroup           Tools
 * @apiName            ValidateDataSynchronizeImport
 * @api                {post} /v1/tools/data-synchronize/import/:type/validate Validate import data by type
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ({type}.import)
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ValidateDataSynchronizeImportController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/{type}/validate', ValidateDataSynchronizeImportController::class)
    ->middleware(['auth:api'])
    ->where('type', '[a-z\-]+');
