<?php

/**
 * @apiGroup           Gallery
 *
 * @apiName            UpdateGallery
 *
 * @api                {patch} /v1/galleries/{gallery_id} Update Gallery
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Gallery\UI\API\Controllers\UpdateGalleryController;
use Illuminate\Support\Facades\Route;

Route::patch('galleries/{gallery_id}', UpdateGalleryController::class)
    ->middleware(['auth:api']);
