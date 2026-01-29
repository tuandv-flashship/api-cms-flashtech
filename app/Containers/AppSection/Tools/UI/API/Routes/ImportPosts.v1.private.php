<?php

/**
 * @apiGroup           Tools
 * @apiName            ImportPosts
 * @api                {post} /v1/tools/data-synchronize/import/posts Import posts
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPostsController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/posts', ImportPostsController::class)
    ->middleware(['auth:api']);
