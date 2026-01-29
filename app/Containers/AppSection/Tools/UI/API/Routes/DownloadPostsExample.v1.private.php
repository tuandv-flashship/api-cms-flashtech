<?php

/**
 * @apiGroup           Tools
 * @apiName            DownloadPostsExample
 * @api                {post} /v1/tools/data-synchronize/import/posts/download-example Download posts example
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\DownloadPostsExampleController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/posts/download-example', DownloadPostsExampleController::class)
    ->middleware(['auth:api']);
