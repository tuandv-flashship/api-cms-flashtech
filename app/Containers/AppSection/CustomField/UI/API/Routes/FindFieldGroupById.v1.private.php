<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            FindFieldGroupById
 *
 * @api                {get} /v1/custom-fields/groups/{field_group_id} Find Field Group
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\FindFieldGroupByIdController;
use Illuminate\Support\Facades\Route;

Route::get('custom-fields/groups/{field_group_id}', FindFieldGroupByIdController::class)
    ->middleware(['auth:api']);
