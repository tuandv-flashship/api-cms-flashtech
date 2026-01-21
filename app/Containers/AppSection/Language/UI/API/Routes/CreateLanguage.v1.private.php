<?php

/**
 * @apiGroup           Language
 *
 * @apiName            CreateLanguage
 *
 * @api                {post} /v1/languages Create Language
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiBody            {String} lang_name
 * @apiBody            {String} lang_locale
 * @apiBody            {String} lang_code
 * @apiBody            {String} [lang_flag]
 * @apiBody            {Boolean} [lang_is_default]
 * @apiBody            {Boolean} [lang_is_rtl]
 * @apiBody            {Number} [lang_order]
 *
 * @apiUse             LanguageSuccessSingleResponse
 */

use App\Containers\AppSection\Language\UI\API\Controllers\CreateLanguageController;
use Illuminate\Support\Facades\Route;

Route::post('languages', CreateLanguageController::class)
    ->middleware(['auth:api']);
