<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            CreatePost
 *
 * @api                {post} /v1/blog/posts Create Post
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\CreatePostController;
use Illuminate\Support\Facades\Route;

Route::post('blog/posts', CreatePostController::class)
    ->middleware(['auth:api']);
