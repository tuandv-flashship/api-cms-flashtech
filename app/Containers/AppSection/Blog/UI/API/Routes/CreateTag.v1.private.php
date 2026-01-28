<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            CreateTag
 *
 * @api                {post} /v1/blog/tags Create Tag
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\CreateTagController;
use Illuminate\Support\Facades\Route;

Route::post('blog/tags', CreateTagController::class)
    ->middleware(['auth:api']);
