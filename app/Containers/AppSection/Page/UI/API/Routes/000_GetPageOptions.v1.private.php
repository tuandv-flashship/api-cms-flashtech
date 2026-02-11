<?php

/**
 * @apiGroup           Page
 *
 * @apiName            GetPageOptions
 *
 * @api                {get} /v1/pages/options Get Page Options
 *
 * @apiDescription     Get master data options for page forms (create/update).
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Page\UI\API\Controllers\GetPageOptionsController;
use Illuminate\Support\Facades\Route;

Route::get('pages/options', GetPageOptionsController::class)
    ->middleware(['auth:api']);
