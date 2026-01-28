<?php

/**
 * @apiGroup           Blog
 *
 * @apiName            RecordPostView
 *
 * @api                {post} /v1/blog/posts/{post_id}/views Record Post View
 *
 * @apiVersion         1.0.0
 *
 * @apiPermission      Authenticated
 *
 * @apiHeader          {String} accept=application/json
 */

use App\Containers\AppSection\Blog\UI\API\Controllers\RecordPostViewController;
use Illuminate\Support\Facades\Route;

Route::post('blog/posts/{post_id}/views', RecordPostViewController::class)
    ->middleware(['auth:api']);
