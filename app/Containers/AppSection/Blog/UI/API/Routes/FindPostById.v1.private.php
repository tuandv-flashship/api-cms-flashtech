<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            FindPostById
 *
 * @api                {get} /v1/blog/posts/{post_id} Find Post
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\FindPostByIdController;
use Illuminate\Support\Facades\Route;

Route::get('blog/posts/{post_id}', FindPostByIdController::class)
    ->middleware(['auth:api']);
