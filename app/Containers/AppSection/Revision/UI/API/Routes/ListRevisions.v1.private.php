<?php

/**
 * @apiGroup           Revision
 * @apiName            ListRevisions
 *
 * @api                {GET} /v1/revisions List revisions
 * @apiDescription     List revisions for a revisionable model.
 *
 * @apiUse             RevisionResponse
 */

use App\Containers\AppSection\Revision\UI\API\Controllers\ListRevisionsController;
use Illuminate\Support\Facades\Route;

Route::get('revisions', ListRevisionsController::class)
    ->middleware(['auth:api']);
