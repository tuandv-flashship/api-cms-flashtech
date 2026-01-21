<?php

/**
 * @apiGroup           Language
 *
 * @apiName            DeleteLanguage
 *
 * @api                {delete} /v1/languages/:language_id Delete Language
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated ['permissions' => null, 'roles' => null]
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 *
 * @apiParam           {String} language_id
 */

use App\Containers\AppSection\Language\UI\API\Controllers\DeleteLanguageController;
use Illuminate\Support\Facades\Route;

Route::delete('languages/{language_id}', DeleteLanguageController::class)
    ->middleware(['auth:api']);
