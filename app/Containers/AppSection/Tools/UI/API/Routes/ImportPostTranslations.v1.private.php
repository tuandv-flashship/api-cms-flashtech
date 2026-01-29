<?php

/**
 * @apiGroup           Tools
 * @apiName            ImportPostTranslations
 * @api                {post} /v1/tools/data-synchronize/import/translations/model Import post translations
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPostTranslationsController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/translations/model', ImportPostTranslationsController::class)
    ->middleware(['auth:api']);
