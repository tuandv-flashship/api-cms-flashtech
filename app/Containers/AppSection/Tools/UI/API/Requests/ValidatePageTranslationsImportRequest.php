<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ValidatePageTranslationsImportRequest extends BaseDataSynchronizeImportRequest
{
    protected string $permission = 'page-translations.import';
}
