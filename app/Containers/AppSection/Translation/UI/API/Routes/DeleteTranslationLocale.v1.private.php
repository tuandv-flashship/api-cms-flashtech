<?php

/**
 * @apiGroup           Translation
 * @apiName            DeleteTranslationLocale
 * @api                {delete} /v1/translations/locales/:locale Delete Translation Locale
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\DeleteTranslationLocaleController;
use Illuminate\Support\Facades\Route;

Route::delete('translations/locales/{locale}', DeleteTranslationLocaleController::class)
    ->middleware(['auth:api']);
