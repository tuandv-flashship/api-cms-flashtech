<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            ListCustomFieldBoxes
 *
 * @api                {get} /v1/custom-fields/boxes List Custom Field Boxes
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\ListCustomFieldBoxesController;
use Illuminate\Support\Facades\Route;

Route::get('custom-fields/boxes', ListCustomFieldBoxesController::class)
    ->middleware(['auth:api']);
