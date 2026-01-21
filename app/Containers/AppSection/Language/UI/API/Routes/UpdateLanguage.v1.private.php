<?php

/**
 * @apiGroup           Language
 *
 * @apiName            UpdateLanguage
 *
 * @api                {patch} /v1/languages/:language_id Update Language
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} language_id
 *
 * @apiBody            {String} [lang_name]
 * @apiBody            {String} [lang_locale]
 * @apiBody            {String} [lang_code]
 * @apiBody            {String} [lang_flag]
 * @apiBody            {Boolean} [lang_is_default]
 * @apiBody            {Boolean} [lang_is_rtl]
 * @apiBody            {Number} [lang_order]
 *
 * @apiUse             LanguageSuccessSingleResponse
 */

use App\Containers\AppSection\Language\UI\API\Controllers\UpdateLanguageController;
use Illuminate\Support\Facades\Route;

Route::patch('languages/{language_id}', UpdateLanguageController::class)
    ->middleware(['auth:api']);
