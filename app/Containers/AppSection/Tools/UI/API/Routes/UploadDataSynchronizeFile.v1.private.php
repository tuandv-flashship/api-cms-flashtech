<?php

/**
 * @apiGroup           Tools
 * @apiName            UploadDataSynchronizeFile
 * @api                {post} /v1/tools/data-synchronize/upload Upload file
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\UploadDataSynchronizeFileController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/upload', UploadDataSynchronizeFileController::class)
    ->middleware(['auth:api']);
