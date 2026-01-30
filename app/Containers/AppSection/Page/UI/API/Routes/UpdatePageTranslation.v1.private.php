<?php

/**
 * @apiGroup           Page
 *
 * @apiName            UpdatePageTranslation
 *
 * @api                {patch} /v1/pages/{page_id}/translations Update Page Translation
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Page\UI\API\Controllers\UpdatePageTranslationController;
use Illuminate\Support\Facades\Route;

Route::patch('pages/{page_id}/translations', UpdatePageTranslationController::class)
    ->middleware(['auth:api']);
