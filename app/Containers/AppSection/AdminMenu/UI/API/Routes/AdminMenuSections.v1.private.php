<?php

/**
 * @apiGroup           AdminMenu
 *
 * @apiName            ListAdminMenuSections
 *
 * @api                {get} /v1/admin-menus/sections List Admin Menu Sections
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      admin-menus.index
 *
 * @apiHeader          {String} accept=application/json
 * @apiHeader          {String} authorization=Bearer
 * @apiHeader          {String} [X-Locale=en] Locale for section name translations
 */

use App\Containers\AppSection\AdminMenu\UI\API\Controllers\ListAdminMenuSectionsController;
use Illuminate\Support\Facades\Route;

Route::get('admin-menus/sections', ListAdminMenuSectionsController::class)
    ->middleware(['auth:api']);
