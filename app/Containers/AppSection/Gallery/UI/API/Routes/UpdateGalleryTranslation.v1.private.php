<?php

/**
 * @apiGroup           Gallery
 *
 * @apiName            UpdateGalleryTranslation
 *
 * @api                {patch} /v1/galleries/{gallery_id}/translations Update Gallery Translation
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Gallery\UI\API\Controllers\UpdateGalleryTranslationController;
use Illuminate\Support\Facades\Route;

Route::patch('galleries/{gallery_id}/translations', UpdateGalleryTranslationController::class)
    ->middleware(['auth:api']);
