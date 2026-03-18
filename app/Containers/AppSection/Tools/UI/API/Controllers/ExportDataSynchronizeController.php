<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\ExportDataSynchronizeAction;
use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\ExportDataSynchronizeRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportDataSynchronizeController extends ApiController
{
    public function __invoke(
        ExportDataSynchronizeRequest $request,
        DataSynchronizeRegistry $registry,
        ExportDataSynchronizeAction $action,
        string $type,
    ): BinaryFileResponse {
        $exporter = $registry->makeExporter($type)
            ->withFilters($request->validated())
            ->acceptedColumns($request->input('columns'))
            ->format($request->input('format', 'csv'));

        return $action->run($exporter);
    }
}
