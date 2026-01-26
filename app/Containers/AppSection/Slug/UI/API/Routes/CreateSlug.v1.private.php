<?php

/**
 * @apiGroup           Slug
 *
 * @apiName            CreateSlug
 *
 * @api                {post} /v1/slugs/create Create Slug
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiBody            {String} value
 * @apiBody            {String} [slug_id]
 * @apiBody            {String} [model]
 */

use App\Containers\AppSection\Slug\UI\API\Controllers\CreateSlugController;
use Illuminate\Support\Facades\Route;

Route::post('slugs/create', CreateSlugController::class)
    ->middleware(['auth:api']);
