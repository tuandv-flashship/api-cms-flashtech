<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\ExportDataSynchronizeAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\ExportPostTranslationsRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportPostTranslationsController extends ApiController
{
    public function __invoke(
        ExportPostTranslationsRequest $request,
        DataSynchronizeRegistry $registry,
        ExportDataSynchronizeAction $action
    ): BinaryFileResponse {
        $exporter = $registry->makeExporter('post-translations', $request->input('class'))
            ->acceptedColumns($request->input('columns'))
            ->format($request->input('format', 'csv'));

        return $action->run($exporter);
    }
}
