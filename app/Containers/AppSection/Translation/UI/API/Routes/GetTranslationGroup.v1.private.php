<?php

/**
 * @apiGroup           Translation
 * @apiName            GetTranslationGroup
 * @api                {get} /v1/translations/:locale/group Get Translation Group
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiParam           {String} group
 * @apiUse             TranslationGroupResponse
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\GetTranslationGroupController;
use Illuminate\Support\Facades\Route;

Route::get('translations/{locale}/group', GetTranslationGroupController::class)
    ->middleware(['auth:api']);
