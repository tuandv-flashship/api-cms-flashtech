<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            DeletePost
 *
 * @api                {delete} /v1/blog/posts/{post_id} Delete Post
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\DeletePostController;
use Illuminate\Support\Facades\Route;

Route::delete('blog/posts/{post_id}', DeletePostController::class)
    ->middleware(['auth:api']);
