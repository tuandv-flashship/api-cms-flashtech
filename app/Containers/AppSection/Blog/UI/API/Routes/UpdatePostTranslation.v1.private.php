<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            UpdatePostTranslation
 *
 * @api                {patch} /v1/blog/posts/{post_id}/translations Update Post Translation
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\UpdatePostTranslationController;
use Illuminate\Support\Facades\Route;

Route::patch('blog/posts/{post_id}/translations', UpdatePostTranslationController::class)
    ->middleware(['auth:api']);
