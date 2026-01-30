<?php

/**
 * @apiGroup           Page
 *
 * @apiName            ListPages
 *
 * @api                {get} /v1/pages List Pages
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Page\UI\API\Controllers\ListPagesController;
use Illuminate\Support\Facades\Route;

Route::get('pages', ListPagesController::class)
    ->middleware(['auth:api']);
