<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            ListTags
 *
 * @api                {get} /v1/blog/tags List Tags
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\ListTagsController;
use Illuminate\Support\Facades\Route;

Route::get('blog/tags', ListTagsController::class)
    ->middleware(['auth:api']);
