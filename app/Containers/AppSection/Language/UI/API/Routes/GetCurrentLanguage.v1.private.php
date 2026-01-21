<?php

/**
 * @apiGroup           Language
 *
 * @apiName            GetCurrentLanguage
 *
 * @api                {get} /v1/languages/current Get Current Language
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiHeader          {String} [X-Locale]
 *
 * @apiUse             LanguageSuccessSingleResponse
 */

use App\Containers\AppSection\Language\Middleware\SetLocaleFromHeader;
use App\Containers\AppSection\Language\UI\API\Controllers\GetCurrentLanguageController;
use Illuminate\Support\Facades\Route;

Route::get('languages/current', GetCurrentLanguageController::class)
    ->middleware(['auth:api', SetLocaleFromHeader::class]);
