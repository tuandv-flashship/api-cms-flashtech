<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            GetCustomFieldOptions
 *
 * @api                {get} /v1/custom-fields/options Get Custom Field Options
 *
 * @apiDescription     Get master data options for custom field forms (create/update).
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\GetCustomFieldOptionsController;
use Illuminate\Support\Facades\Route;

Route::get('custom-fields/options', GetCustomFieldOptionsController::class)
    ->middleware(['auth:api']);
