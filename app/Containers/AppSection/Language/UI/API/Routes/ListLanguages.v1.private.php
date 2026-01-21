<?php

/**
 * @apiGroup           Language
 *
 * @apiName            ListLanguages
 *
 * @api                {get} /v1/languages List Languages
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiHeader          {String} [X-Locale]
 *
 * @apiUse             LanguageSuccessMultipleResponse
 */

use App\Containers\AppSection\Language\Middleware\SetLocaleFromHeader;
use App\Containers\AppSection\Language\UI\API\Controllers\ListLanguagesController;
use Illuminate\Support\Facades\Route;

Route::get('languages', ListLanguagesController::class)
    ->middleware(['auth:api', SetLocaleFromHeader::class]);
