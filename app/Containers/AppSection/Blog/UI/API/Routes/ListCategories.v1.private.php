<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            ListCategories
 *
 * @api                {get} /v1/blog/categories List Categories
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\ListCategoriesController;
use Illuminate\Support\Facades\Route;

Route::get('blog/categories', ListCategoriesController::class)
    ->middleware(['auth:api']);
