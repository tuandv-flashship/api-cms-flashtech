<?php

/**
 * @apiGroup           Page
 *
 * @apiName            UpdatePage
 *
 * @api                {patch} /v1/pages/{page_id} Update Page
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Page\UI\API\Controllers\UpdatePageController;
use Illuminate\Support\Facades\Route;

Route::patch('pages/{page_id}', UpdatePageController::class)
    ->middleware(['auth:api']);
