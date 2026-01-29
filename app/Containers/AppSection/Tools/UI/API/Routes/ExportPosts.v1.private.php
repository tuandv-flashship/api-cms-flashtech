<?php

/**
 * @apiGroup           Tools
 * @apiName            ExportPosts
 * @api                {post} /v1/tools/data-synchronize/export/posts Export posts
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPostsController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/export/posts', ExportPostsController::class)
    ->middleware(['auth:api']);
