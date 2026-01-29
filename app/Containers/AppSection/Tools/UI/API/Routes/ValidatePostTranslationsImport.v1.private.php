<?php

/**
 * @apiGroup           Tools
 * @apiName            ValidatePostTranslationsImport
 * @api                {post} /v1/tools/data-synchronize/import/translations/model/validate Validate post translations import
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePostTranslationsImportController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/translations/model/validate', ValidatePostTranslationsImportController::class)
    ->middleware(['auth:api']);
