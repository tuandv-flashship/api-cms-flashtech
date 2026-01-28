<?php

/**
 * @apiGroup           Gallery
 *
 * @apiName            DeleteGallery
 *
 * @api                {delete} /v1/galleries/{gallery_id} Delete Gallery
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Gallery\UI\API\Controllers\DeleteGalleryController;
use Illuminate\Support\Facades\Route;

Route::delete('galleries/{gallery_id}', DeleteGalleryController::class)
    ->middleware(['auth:api']);
