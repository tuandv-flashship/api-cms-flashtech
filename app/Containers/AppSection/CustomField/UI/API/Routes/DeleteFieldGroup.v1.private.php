<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            DeleteFieldGroup
 *
 * @api                {delete} /v1/custom-fields/groups/{field_group_id} Delete Field Group
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\DeleteFieldGroupController;
use Illuminate\Support\Facades\Route;

Route::delete('custom-fields/groups/{field_group_id}', DeleteFieldGroupController::class)
    ->middleware(['auth:api']);
