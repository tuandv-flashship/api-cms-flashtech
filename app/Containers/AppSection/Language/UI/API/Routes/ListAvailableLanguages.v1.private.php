<?php

/**
 * @apiGroup           Language
 *
 * @apiName            ListAvailableLanguages
 *
 * @api                {get} /v1/languages/available List Available Languages
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiUse             AvailableLanguageSuccessMultipleResponse
 */

use App\Containers\AppSection\Language\UI\API\Controllers\ListAvailableLanguagesController;
use Illuminate\Support\Facades\Route;

Route::get('languages/available', ListAvailableLanguagesController::class)
    ->middleware(['auth:api']);
