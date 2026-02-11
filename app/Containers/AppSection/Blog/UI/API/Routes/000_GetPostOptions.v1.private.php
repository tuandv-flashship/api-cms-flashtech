<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            GetPostOptions
 *
 * @api                {get} /v1/blog/posts/options Get Post Options
 *
 * @apiDescription     Get master data options for post forms (create/update).
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\GetPostOptionsController;
use Illuminate\Support\Facades\Route;

Route::get('blog/posts/options', GetPostOptionsController::class)
    ->middleware(['auth:api']);
