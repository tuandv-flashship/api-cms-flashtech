<?php

/**
 * @apiGroup           Tools
 * @apiName            DownloadPagesExample
 * @api                {post} /v1/tools/data-synchronize/import/pages/download-example Download pages example
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPagesExampleController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/pages/download-example', DownloadPagesExampleController::class)
    ->middleware(['auth:api']);
