<?php

/**
 * @apiGroup           Tools
 * @apiName            ImportPages
 * @api                {post} /v1/tools/data-synchronize/import/pages Import pages
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ImportPagesController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/pages', ImportPagesController::class)
    ->middleware(['auth:api']);
