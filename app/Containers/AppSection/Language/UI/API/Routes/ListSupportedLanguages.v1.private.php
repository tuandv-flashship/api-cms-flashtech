<?php

/**
 * @apiGroup           Language
 *
 * @apiName            ListSupportedLanguages
 *
 * @api                {get} /v1/languages/supported List Supported Languages
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

use App\Containers\AppSection\Language\UI\API\Controllers\ListSupportedLanguagesController;
use Illuminate\Support\Facades\Route;

Route::get('languages/supported', ListSupportedLanguagesController::class)
    ->middleware(['auth:api']);
