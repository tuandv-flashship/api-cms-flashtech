<?php

/**
 * @apiGroup           Tools
 * @apiName            ImportOtherTranslations
 * @api                {post} /v1/tools/data-synchronize/import/other-translations Import other translations
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ImportOtherTranslationsController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/other-translations', ImportOtherTranslationsController::class)
    ->middleware(['auth:api']);
