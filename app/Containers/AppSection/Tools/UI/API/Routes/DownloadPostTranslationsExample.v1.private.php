<?php

/**
 * @apiGroup           Tools
 * @apiName            DownloadPostTranslationsExample
 * @api                {post} /v1/tools/data-synchronize/import/translations/model/download-example Download post translations example
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPostTranslationsExampleController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/translations/model/download-example', DownloadPostTranslationsExampleController::class)
    ->middleware(['auth:api']);
