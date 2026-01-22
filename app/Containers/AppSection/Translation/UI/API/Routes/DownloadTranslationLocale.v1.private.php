<?php

/**
 * @apiGroup           Translation
 * @apiName            DownloadTranslationLocale
 * @api                {get} /v1/translations/locales/:locale/download Download Translation Locale
 * @apiVersion         1.0.0
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\DownloadTranslationLocaleController;
use Illuminate\Support\Facades\Route;

Route::get('translations/locales/{locale}/download', DownloadTranslationLocaleController::class)
    ->middleware(['auth:api']);
