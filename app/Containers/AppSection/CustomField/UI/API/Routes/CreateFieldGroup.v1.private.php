<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            CreateFieldGroup
 *
 * @api                {post} /v1/custom-fields/groups Create Field Group
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\CreateFieldGroupController;
use Illuminate\Support\Facades\Route;

Route::post('custom-fields/groups', CreateFieldGroupController::class)
    ->middleware(['auth:api']);
