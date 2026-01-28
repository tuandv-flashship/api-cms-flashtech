<?php

/**
 * @apiGroup           Gallery
 *
 * @apiName            CreateGallery
 *
 * @api                {post} /v1/galleries Create Gallery
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Gallery\UI\API\Controllers\CreateGalleryController;
use Illuminate\Support\Facades\Route;

Route::post('galleries', CreateGalleryController::class)
    ->middleware(['auth:api']);
