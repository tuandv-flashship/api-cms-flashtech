<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            GetBlogReport
 *
 * @api                {get} /v1/blog/reports Get Blog Report
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\GetBlogReportController;
use Illuminate\Support\Facades\Route;

Route::get('blog/reports', GetBlogReportController::class)
    ->middleware(['auth:api']);
