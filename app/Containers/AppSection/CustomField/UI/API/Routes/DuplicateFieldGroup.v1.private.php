<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            DuplicateFieldGroup
 *
 * @api                {post} /v1/custom-field-groups/:id/duplicate Duplicate Field Group
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      custom-fields.create
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\DuplicateFieldGroupController;
use Illuminate\Support\Facades\Route;

Route::post('custom-field-groups/{field_group_id}/duplicate', DuplicateFieldGroupController::class)
    ->middleware(['auth:api']);
