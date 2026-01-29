<?php

/**
 * @apiGroup           Tools
 * @apiName            ValidatePostsImport
 * @api                {post} /v1/tools/data-synchronize/import/posts/validate Validate posts import
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated
 */

use App\Containers\AppSection\Tools\UI\API\Controllers\ValidatePostsImportController;
use Illuminate\Support\Facades\Route;

Route::post('tools/data-synchronize/import/posts/validate', ValidatePostsImportController::class)
    ->middleware(['auth:api']);
