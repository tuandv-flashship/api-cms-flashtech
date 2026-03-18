<?php

/**
 * @apiGroup           Tools
 * @apiName            ImportPageTranslations
 * @api                {post} /v1/tools/data-synchronize/import/translations/page Import page translations
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPageTranslationsController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/translations/page', ImportPageTranslationsController::class)
    ->middleware(['auth:api']);
