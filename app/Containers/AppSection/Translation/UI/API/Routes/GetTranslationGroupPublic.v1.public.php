<?php

/**
 * @apiGroup           Translation
 *
 * @apiName            GetTranslationGroupPublic
 *
 * @api                {get} /v1/translations/:locale/:group Get Translation Group (Public)
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      none
 *
 * @apiHeader          {String} accept=application/json
 *
 * @apiParam           {String} locale Locale code (e.g. vi, en)
 * @apiParam           {String} group  Group name (e.g. validation, blog, *)
 *
 * @apiSuccess         {Object} data Translations for the group
 * @apiSuccess         {Object} meta
 * @apiSuccess         {String} meta.locale
 * @apiSuccess         {String} meta.group
 * @apiSuccess         {Number} meta.total_keys
 */

use App\Containers\AppSection\Translation\UI\API\Controllers\GetTranslationGroupPublicController;
use Illuminate\Support\Facades\Route;

Route::get('translations/{locale}/{group}', GetTranslationGroupPublicController::class)
    ->where('locale', '^(?!locales$)[a-z]{2}(_[A-Za-z]+)?$')
    ->where('group', '^(?!groups?$|json$|locales$).+$');
