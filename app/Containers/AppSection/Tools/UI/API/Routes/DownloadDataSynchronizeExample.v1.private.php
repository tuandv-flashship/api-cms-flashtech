<?php

/**
 * @apiGroup           Tools
 * @apiName            DownloadDataSynchronizeExample
 * @api                {post} /v1/tools/data-synchronize/import/:type/download-example Download example file by type
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ({type}.import)
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadDataSynchronizeExampleController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/{type}/download-example', DownloadDataSynchronizeExampleController::class)
    ->middleware(['auth:api'])
    ->where('type', '[a-z\-]+');
