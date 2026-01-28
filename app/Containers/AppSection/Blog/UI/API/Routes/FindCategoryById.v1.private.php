<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            FindCategoryById
 *
 * @api                {get} /v1/blog/categories/{category_id} Find Category
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\FindCategoryByIdController;
use Illuminate\Support\Facades\Route;

Route::get('blog/categories/{category_id}', FindCategoryByIdController::class)
    ->middleware(['auth:api']);
