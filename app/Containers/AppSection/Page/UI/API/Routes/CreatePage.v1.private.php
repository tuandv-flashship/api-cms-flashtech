<?php

/**
 * @apiGroup           Page
 *
 * @apiName            CreatePage
 *
 * @api                {post} /v1/pages Create Page
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Page\UI\API\Controllers\CreatePageController;
use Illuminate\Support\Facades\Route;

Route::post('pages', CreatePageController::class)
    ->middleware(['auth:api']);
