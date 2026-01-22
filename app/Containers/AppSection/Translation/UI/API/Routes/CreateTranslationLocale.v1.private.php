<?php

/**
 * @apiGroup           Translation
 * @apiName            CreateTranslationLocale
 * @api                {post} /v1/translations/locales Create Translation Locale
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiBody            {String} locale
 * @apiBody            {String} [source] github|copy
 * @apiBody            {Boolean} [include_vendor]
 * @apiUse             TranslationLocaleStatusResponse
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\CreateTranslationLocaleController;
use Illuminate\Support\Facades\Route;

Route::post('translations/locales', CreateTranslationLocaleController::class)
    ->middleware(['auth:api']);
