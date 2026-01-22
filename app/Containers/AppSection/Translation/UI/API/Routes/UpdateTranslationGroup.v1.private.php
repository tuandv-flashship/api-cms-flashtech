<?php

/**
 * @apiGroup           Translation
 * @apiName            UpdateTranslationGroup
 * @api                {patch} /v1/translations/:locale/group Update Translation Group
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiBody            {String} group
 * @apiBody            {Object} translations
 * @apiUse             TranslationGroupResponse
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\UpdateTranslationGroupController;
use Illuminate\Support\Facades\Route;

Route::patch('translations/{locale}/group', UpdateTranslationGroupController::class)
    ->middleware(['auth:api']);
