<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ImportPageTranslationsRequest extends BaseDataSynchronizeImportRequest
{
    protected string $permission = 'page-translations.import';
}
