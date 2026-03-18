<?php

/**
 * @apiGroup           Tools
 * @apiName            ValidatePageTranslationsImport
 * @api                {post} /v1/tools/data-synchronize/import/translations/page/validate Validate page translations import
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePageTranslationsImportController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/translations/page/validate', ValidatePageTranslationsImportController::class)
    ->middleware(['auth:api']);
