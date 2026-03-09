<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            ImportFieldGroup
 *
 * @api                {post} /v1/custom-field-groups/import Import Field Group
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      custom-fields.create
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} content-type=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\ImportFieldGroupController;
use Illuminate\Support\Facades\Route;

Route::post('custom-field-groups/import', ImportFieldGroupController::class)
    ->middleware(['auth:api']);
