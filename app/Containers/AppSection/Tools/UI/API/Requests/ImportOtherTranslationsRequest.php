<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ImportOtherTranslationsRequest extends BaseDataSynchronizeImportRequest
{
    protected string $permission = 'other-translations.import';
}
