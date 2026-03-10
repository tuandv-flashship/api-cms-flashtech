<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            ExportFieldGroup
 *
 * @api                {get} /v1/custom-fields/groups/:id/export Export Field Group
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      custom-fields.index
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\ExportFieldGroupController;
use Illuminate\Support\Facades\Route;

Route::get('custom-fields/groups/{field_group_id}/export', ExportFieldGroupController::class)
    ->middleware(['auth:api']);
