<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            CreateCategory
 *
 * @api                {post} /v1/blog/categories Create Category
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\CreateCategoryController;
use Illuminate\Support\Facades\Route;

Route::post('blog/categories', CreateCategoryController::class)
    ->middleware(['auth:api']);
