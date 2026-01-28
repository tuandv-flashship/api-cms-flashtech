<?php

/**
 * @apiGroup           Gallery
 *
 * @apiName            ListGalleries
 *
 * @api                {get} /v1/galleries List Galleries
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Gallery\UI\API\Controllers\ListGalleriesController;
use Illuminate\Support\Facades\Route;

Route::get('galleries', ListGalleriesController::class)
    ->middleware(['auth:api']);
