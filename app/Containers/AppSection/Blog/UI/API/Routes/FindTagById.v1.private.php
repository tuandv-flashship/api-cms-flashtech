<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            FindTagById
 *
 * @api                {get} /v1/blog/tags/{tag_id} Find Tag
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\FindTagByIdController;
use Illuminate\Support\Facades\Route;

Route::get('blog/tags/{tag_id}', FindTagByIdController::class)
    ->middleware(['auth:api']);
