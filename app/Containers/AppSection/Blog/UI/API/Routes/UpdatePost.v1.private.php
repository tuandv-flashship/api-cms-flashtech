<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            UpdatePost
 *
 * @api                {patch} /v1/blog/posts/{post_id} Update Post
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\UpdatePostController;
use Illuminate\Support\Facades\Route;

Route::patch('blog/posts/{post_id}', UpdatePostController::class)
    ->middleware(['auth:api']);
