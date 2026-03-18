<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ValidatePagesImportRequest extends BaseDataSynchronizeImportRequest
{
    protected string $permission = 'pages.import';
}
