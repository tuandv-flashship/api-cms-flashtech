<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            DeleteTag
 *
 * @api                {delete} /v1/blog/tags/{tag_id} Delete Tag
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\DeleteTagController;
use Illuminate\Support\Facades\Route;

Route::delete('blog/tags/{tag_id}', DeleteTagController::class)
    ->middleware(['auth:api']);
