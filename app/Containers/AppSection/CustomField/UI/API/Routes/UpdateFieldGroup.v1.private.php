<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            UpdateFieldGroup
 *
 * @api                {patch} /v1/custom-fields/groups/{field_group_id} Update Field Group
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\UpdateFieldGroupController;
use Illuminate\Support\Facades\Route;

Route::patch('custom-fields/groups/{field_group_id}', UpdateFieldGroupController::class)
    ->middleware(['auth:api']);
