<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ValidateOtherTranslationsImportRequest extends BaseDataSynchronizeImportRequest
{
    protected string $permission = 'other-translations.import';
}
