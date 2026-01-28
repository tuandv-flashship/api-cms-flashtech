<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            UpdateCategoryTranslation
 *
 * @api                {patch} /v1/blog/categories/{category_id}/translations Update Category Translation
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\UpdateCategoryTranslationController;
use Illuminate\Support\Facades\Route;

Route::patch('blog/categories/{category_id}/translations', UpdateCategoryTranslationController::class)
    ->middleware(['auth:api']);
