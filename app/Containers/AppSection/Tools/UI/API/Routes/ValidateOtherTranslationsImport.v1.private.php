<?php

/**
 * @apiGroup           Tools
 * @apiName            ValidateOtherTranslationsImport
 * @api                {post} /v1/tools/data-synchronize/import/other-translations/validate Validate other translations import
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ValidateOtherTranslationsImportController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/other-translations/validate', ValidateOtherTranslationsImportController::class)
    ->middleware(['auth:api']);
