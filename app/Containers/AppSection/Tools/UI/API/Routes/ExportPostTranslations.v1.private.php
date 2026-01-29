<?php

/**
 * @apiGroup           Tools
 * @apiName            ExportPostTranslations
 * @api                {post} /v1/tools/data-synchronize/export/translations/model Export post translations
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPostTranslationsController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/export/translations/model', ExportPostTranslationsController::class)
    ->middleware(['auth:api']);
