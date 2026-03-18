<?php

/**
 * @apiGroup           Tools
 * @apiName            DownloadPageTranslationsExample
 * @api                {post} /v1/tools/data-synchronize/import/translations/page/download-example Download page translations example
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPageTranslationsExampleController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/translations/page/download-example', DownloadPageTranslationsExampleController::class)
    ->middleware(['auth:api']);
