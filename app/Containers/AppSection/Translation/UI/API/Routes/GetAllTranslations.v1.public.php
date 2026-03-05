<?php

/**
 * @apiGroup           Translation
 *
 * @apiName            GetAllTranslations
 *
 * @api                {get} /v1/translations/:locale Get All Translations
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      none
 *
 * @apiHeader          {String} accept=application/json
 *
 * @apiParam           {String} locale Locale code (e.g. vi, en)
 *
 * @apiSuccess         {Object} data Translations grouped by group_key
 * @apiSuccess         {Object} meta
 * @apiSuccess         {String} meta.locale
 * @apiSuccess         {Number} meta.total_keys
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\GetAllTranslationsController;
use Illuminate\Support\Facades\Route;

Route::get('translations/{locale}', GetAllTranslationsController::class)
    ->where('locale', '^(?!locales$)[a-z]{2}(_[A-Za-z]+)?$');
