<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\ExportDataSynchronizeAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\ExportOtherTranslationsRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportOtherTranslationsController extends ApiController
{
    public function __invoke(
        ExportOtherTranslationsRequest $request,
        DataSynchronizeRegistry $registry,
        ExportDataSynchronizeAction $action
    ): BinaryFileResponse {
        $exporter = $registry->makeExporter('other-translations')
            ->acceptedColumns($request->input('columns'))
            ->format($request->input('format', 'csv'));

        return $action->run($exporter);
    }
}
