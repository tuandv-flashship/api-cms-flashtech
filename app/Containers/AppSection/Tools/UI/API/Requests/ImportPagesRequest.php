<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ImportPagesRequest extends BaseDataSynchronizeImportRequest
{
    protected string $permission = 'pages.import';
}
