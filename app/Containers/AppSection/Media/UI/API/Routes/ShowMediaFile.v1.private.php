<?php

/**
 * @apiGroup           Media
 *
 * @apiName            ShowMediaFile
 *
 * @api                {get} /v1/media/files/{hash}/{id} Show Media File
 *
 * @apiVersion         1.0.0
 */

use App\Containers\AppSection\Media\UI\API\Controllers\ShowMediaFileController;
use Illuminate\Support\Facades\Route;

Route::get('media/files/{hash}/{id}', ShowMediaFileController::class)
    ->name('media.indirect.url');
