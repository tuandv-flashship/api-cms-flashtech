<?php

/**
 * @apiGroup           Page
 *
 * @apiName            FindPageById
 *
 * @api                {get} /v1/pages/{page_id} Find Page
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Page\UI\API\Controllers\FindPageByIdController;
use Illuminate\Support\Facades\Route;

Route::get('pages/{page_id}', FindPageByIdController::class)
    ->middleware(['auth:api']);
