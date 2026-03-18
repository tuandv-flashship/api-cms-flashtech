<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\ExportDataSynchronizeAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\ExportPagesRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportPagesController extends ApiController
{
    public function __invoke(
        ExportPagesRequest $request,
        DataSynchronizeRegistry $registry,
        ExportDataSynchronizeAction $action,
    ): BinaryFileResponse {
        $exporter = $registry->makeExporter('pages')
            ->withFilters($request->validated())
            ->acceptedColumns($request->input('columns'))
            ->format($request->input('format', 'csv'));

        return $action->run($exporter);
    }
}
