<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class ExportOtherTranslationsRequest extends BaseDataSynchronizeExportRequest
{
    protected string $permission = 'other-translations.export';
}
