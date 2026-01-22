<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use App\Containers\AppSection\Translation\Actions\DownloadTranslationLocaleAction;
use App\Containers\AppSection\Translation\UI\API\Requests\DownloadTranslationLocaleRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class DownloadTranslationLocaleController extends ApiController
{
    public function __invoke(DownloadTranslationLocaleRequest $request, DownloadTranslationLocaleAction $action): BinaryFileResponse
    {
        $locale = (string) $request->route('locale');
        $path = $action->run($locale);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
