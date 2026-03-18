<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

final class DownloadPageTranslationsExampleRequest extends BaseDataSynchronizeExampleRequest
{
    protected string $permission = 'page-translations.import';
}
