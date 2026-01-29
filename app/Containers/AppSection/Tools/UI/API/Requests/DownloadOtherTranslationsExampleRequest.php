<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class DownloadOtherTranslationsExampleRequest extends BaseDataSynchronizeExampleRequest
{
    protected string $permission = 'other-translations.import';
}
