<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            UpdateCategory
 *
 * @api                {patch} /v1/blog/categories/{category_id} Update Category
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\UpdateCategoryController;
use Illuminate\Support\Facades\Route;

Route::patch('blog/categories/{category_id}', UpdateCategoryController::class)
    ->middleware(['auth:api']);
