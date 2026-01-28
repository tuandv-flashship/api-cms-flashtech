<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            DeleteCategory
 *
 * @api                {delete} /v1/blog/categories/{category_id} Delete Category
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\DeleteCategoryController;
use Illuminate\Support\Facades\Route;

Route::delete('blog/categories/{category_id}', DeleteCategoryController::class)
    ->middleware(['auth:api']);
