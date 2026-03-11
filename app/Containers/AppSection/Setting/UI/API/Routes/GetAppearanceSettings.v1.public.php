<?php

/**
 * @apiGroup           Setting
 *
 * @apiName            GetAppearanceSettings
 *
 * @api                {get} /v1/settings/appearance Get Appearance Settings (Public)
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      none
 *
 * @apiHeader          {String} accept=application/json
 *
 * @apiUse             AppearanceSettingsResponse
 */

use App\Containers\AppSection\Setting\UI\API\Controllers\GetAppearanceSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('settings/appearance', GetAppearanceSettingsController::class);
