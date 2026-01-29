<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ImportPostsRequest extends BaseDataSynchronizeImportRequest
{
    protected string $permission = 'posts.import';
}
