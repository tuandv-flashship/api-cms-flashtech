<?php

/**
 * @apiGroup           CustomField
 *
 * @apiName            ListFieldGroups
 *
 * @api                {get} /v1/custom-fields/groups List Field Groups
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\CustomField\UI\API\Controllers\ListFieldGroupsController;
use Illuminate\Support\Facades\Route;

Route::get('custom-fields/groups', ListFieldGroupsController::class)
    ->middleware(['auth:api']);
