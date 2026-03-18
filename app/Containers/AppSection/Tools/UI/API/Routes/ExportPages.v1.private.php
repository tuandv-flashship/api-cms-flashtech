<?php

/**
 * @apiGroup           Tools
 * @apiName            ExportPages
 * @api                {post} /v1/tools/data-synchronize/export/pages Export pages
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ExportPagesController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/export/pages', ExportPagesController::class)
    ->middleware(['auth:api']);
