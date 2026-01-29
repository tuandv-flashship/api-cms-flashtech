<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Actions\ExportDataSynchronizeAction;
use App\Containers\AppSection\Tools\Exporters\PostsExporter;
use App\Containers\AppSection\Tools\UI\API\Requests\ExportPostsRequest;
use App\Ship\Parents\Controllers\ApiController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class ExportPostsController extends ApiController
{
    public function __invoke(ExportPostsRequest $request, ExportDataSynchronizeAction $action): BinaryFileResponse
    {
        $exporter = app(PostsExporter::class)
            ->withFilters($request->validated())
            ->acceptedColumns($request->input('columns'))
            ->format($request->input('format', 'csv'));

        return $action->run($exporter);
    }
}
