<?php

/**
 * @apiGroup           Tools
 * @apiName            DownloadOtherTranslationsExample
 * @api                {post} /v1/tools/data-synchronize/import/other-translations/download-example Download other translations example
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadOtherTranslationsExampleController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/other-translations/download-example', DownloadOtherTranslationsExampleController::class)
    ->middleware(['auth:api']);
