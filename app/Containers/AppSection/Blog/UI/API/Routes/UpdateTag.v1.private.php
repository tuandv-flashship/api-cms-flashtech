<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            UpdateTag
 *
 * @api                {patch} /v1/blog/tags/{tag_id} Update Tag
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\UpdateTagController;
use Illuminate\Support\Facades\Route;

Route::patch('blog/tags/{tag_id}', UpdateTagController::class)
    ->middleware(['auth:api']);
