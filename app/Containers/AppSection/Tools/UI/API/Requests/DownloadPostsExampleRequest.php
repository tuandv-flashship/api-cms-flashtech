<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class DownloadPostsExampleRequest extends BaseDataSynchronizeExampleRequest
{
    protected string $permission = 'posts.import';
}
