<?php

/**
 * @apiGroup           Tools
 * @apiName            ValidatePagesImport
 * @api                {post} /v1/tools/data-synchronize/import/pages/validate Validate pages import
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePagesImportController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/pages/validate', ValidatePagesImportController::class)
    ->middleware(['auth:api']);
