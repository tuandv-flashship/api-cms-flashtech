<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            UpdateTagTranslation
 *
 * @api                {patch} /v1/blog/tags/{tag_id}/translations Update Tag Translation
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\UpdateTagTranslationController;
use Illuminate\Support\Facades\Route;

Route::patch('blog/tags/{tag_id}/translations', UpdateTagTranslationController::class)
    ->middleware(['auth:api']);
