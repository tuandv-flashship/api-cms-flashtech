<?php

/**
 * @apiGroup           Tools
 * @apiName            ExportPageTranslations
 * @api                {post} /v1/tools/data-synchronize/export/translations/page Export page translations
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPageTranslationsController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/export/translations/page', ExportPageTranslationsController::class)
    ->middleware(['auth:api']);
