<?php

/**
 * @apiGroup           Translation
 * @apiName            ListTranslationLocales
 * @api                {get} /v1/translations/locales List Translation Locales
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiUse             TranslationLocalesResponse
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\ListTranslationLocalesController;
use Illuminate\Support\Facades\Route;

Route::get('translations/locales', ListTranslationLocalesController::class)
    ->middleware(['auth:api']);
