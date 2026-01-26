<?php

/**
 * @apiGroup           LanguageAdvanced
 *
 * @apiName            UpdateSlugTranslation
 *
 * @api                {patch} /v1/slugs/{slug_id}/translations Update Slug Translation
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiBody            {String} lang_code
 * @apiBody            {String} key
 * @apiBody            {String} [model]
 * @apiBody            {String} [slug]
 * @apiBody            {String} [language]
 */

use App\Containers\AppSection\LanguageAdvanced\UI\API\Controllers\UpdateSlugTranslationController;
use Illuminate\Support\Facades\Route;

Route::patch('slugs/{slug_id}/translations', UpdateSlugTranslationController::class)
    ->middleware(['auth:api']);
