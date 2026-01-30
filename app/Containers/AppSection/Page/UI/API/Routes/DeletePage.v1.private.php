<?php

/**
 * @apiGroup           Page
 *
 * @apiName            DeletePage
 *
 * @api                {delete} /v1/pages/{page_id} Delete Page
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Page\UI\API\Controllers\DeletePageController;
use Illuminate\Support\Facades\Route;

Route::delete('pages/{page_id}', DeletePageController::class)
    ->middleware(['auth:api']);
