<?php

/**
 * @apiGroup           Language
 *
 * @apiName            ListLanguagesPublic
 *
 * @api                {get} /v1/languages List Languages (Public)
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      none
 *
 * @apiHeader          {String} accept=application/json
 *
 * @apiUse             LanguageSuccessMultipleResponse
 */

use App\Containers\AppSection\Language\UI\API\Controllers\ListLanguagesController;
use Illuminate\Support\Facades\Route;

Route::get('languages', ListLanguagesController::class);
