<?php

/**
 * @apiGroup           Translation
 * @apiName            UpdateTranslationJson
 * @api                {patch} /v1/translations/:locale/json Update Translation JSON
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiBody            {Object} translations
 * @apiUse             TranslationGroupResponse
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\UpdateTranslationJsonController;
use Illuminate\Support\Facades\Route;

Route::patch('translations/{locale}/json', UpdateTranslationJsonController::class)
    ->middleware(['auth:api']);
