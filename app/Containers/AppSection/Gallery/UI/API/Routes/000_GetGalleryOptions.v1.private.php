<?php

/**
 * @apiGroup           Gallery
 *
 * @apiName            GetGalleryOptions
 *
 * @api                {get} /v1/galleries/options Get Gallery Options
 *
 * @apiDescription     Get master data options for gallery forms (create/update).
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Gallery\UI\API\Controllers\GetGalleryOptionsController;
use Illuminate\Support\Facades\Route;

Route::get('galleries/options', GetGalleryOptionsController::class)
    ->middleware(['auth:api']);
