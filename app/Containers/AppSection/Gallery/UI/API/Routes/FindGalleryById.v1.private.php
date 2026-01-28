<?php

/**
 * @apiGroup           Gallery
 *
 * @apiName            FindGalleryById
 *
 * @api                {get} /v1/galleries/{gallery_id} Find Gallery
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Gallery\UI\API\Controllers\FindGalleryByIdController;
use Illuminate\Support\Facades\Route;

Route::get('galleries/{gallery_id}', FindGalleryByIdController::class)
    ->middleware(['auth:api']);
