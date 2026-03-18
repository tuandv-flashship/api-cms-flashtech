<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\UI\API\Requests\ListDataSynchronizeTypesRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListDataSynchronizeTypesController extends ApiController
{
    public function __invoke(
        ListDataSynchronizeTypesRequest $request,
        DataSynchronizeRegistry $registry,
    ): JsonResponse {
        $types = [];

        foreach ($registry->getAvailableTypes() as $type) {
            $exporter = $registry->makeExporter($type);
            $types[] = [
                'type' => $type,
                'label' => __("data-synchronize.types.{$type}.label", [], app()->getLocale()) !== "data-synchronize.types.{$type}.label"
                    ? __("data-synchronize.types.{$type}.label")
                    : $exporter->label(),
                'total' => $exporter->getTotal(),
                'export_description' => __("data-synchronize.types.{$type}.export_description"),
                'import_description' => __("data-synchronize.types.{$type}.import_description"),
            ];
        }

        return response()->json(['data' => $types]);
    }
}
