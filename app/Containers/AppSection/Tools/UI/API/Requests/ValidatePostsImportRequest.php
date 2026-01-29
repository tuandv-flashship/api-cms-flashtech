<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ValidatePostsImportRequest extends BaseDataSynchronizeImportRequest
{
    protected string $permission = 'posts.import';
}
