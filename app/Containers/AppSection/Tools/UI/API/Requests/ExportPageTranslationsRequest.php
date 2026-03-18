<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ExportPageTranslationsRequest extends BaseDataSynchronizeExportRequest
{
    protected string $permission = 'page-translations.export';
}
