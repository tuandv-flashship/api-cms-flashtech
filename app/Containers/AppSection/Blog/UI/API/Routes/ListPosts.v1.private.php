<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            ListPosts
 *
 * @api                {get} /v1/blog/posts List Posts
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\ListPostsController;
use Illuminate\Support\Facades\Route;

Route::get('blog/posts', ListPostsController::class)
    ->middleware(['auth:api']);
