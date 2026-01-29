<?php

/**
 * @apiGroup           Tools
 * @apiName            ExportOtherTranslations
 * @api                {post} /v1/tools/data-synchronize/export/other-translations Export other translations
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ExportOtherTranslationsController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/export/other-translations', ExportOtherTranslationsController::class)
    ->middleware(['auth:api']);
