<?php

/**
 * @apiGroup           Translation
 * @apiName            ListTranslationGroups
 * @api                {get} /v1/translations/:locale/groups List Translation Groups
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiUse             TranslationGroupListResponse
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\ListTranslationGroupsController;
use Illuminate\Support\Facades\Route;

Route::get('translations/{locale}/groups', ListTranslationGroupsController::class)
    ->middleware(['auth:api']);
