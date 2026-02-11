<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            GetTagOptions
 *
 * @api                {get} /v1/blog/tags/options Get Tag Options
 *
 * @apiDescription     Get master data options for tag forms (create/update).
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\GetTagOptionsController;
use Illuminate\Support\Facades\Route;

Route::get('blog/tags/options', GetTagOptionsController::class)
    ->middleware(['auth:api']);
