<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            ListCategoriesTree
 *
 * @api                {get} /v1/blog/categories/tree List Categories Tree
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\ListCategoriesTreeController;
use Illuminate\Support\Facades\Route;

Route::get('blog/categories/tree', ListCategoriesTreeController::class)
    ->middleware(['auth:api']);
