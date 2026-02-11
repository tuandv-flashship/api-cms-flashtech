<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            GetCategoryOptions
 *
 * @api                {get} /v1/blog/categories/options Get Category Options
 *
 * @apiDescription     Get master data options for category forms (create/update).
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\GetCategoryOptionsController;
use Illuminate\Support\Facades\Route;

Route::get('blog/categories/options', GetCategoryOptionsController::class)
    ->middleware(['auth:api']);
