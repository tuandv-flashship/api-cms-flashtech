<?php

/**
 * @apiGroup           Language
 *
 * @apiName            SetDefaultLanguage
 *
 * @api                {post} /v1/languages/:language_id/default Set Default Language
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} language_id
 *
 * @apiUse             LanguageSuccessSingleResponse
 */

use App\Containers\AppSection\Language\UI\API\Controllers\SetDefaultLanguageController;
use Illuminate\Support\Facades\Route;

Route::post('languages/{language_id}/default', SetDefaultLanguageController::class)
    ->middleware(['auth:api']);
