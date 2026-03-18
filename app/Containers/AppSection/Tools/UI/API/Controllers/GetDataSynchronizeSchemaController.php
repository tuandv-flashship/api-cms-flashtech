<?php

namespace App\Containers\AppSection\Tools\UI\API\Controllers;

use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Containers\AppSection\Tools\Supports\Export\ExportColumn;
use App\Containers\AppSection\Tools\Supports\Import\ImportColumn;
use App\Containers\AppSection\Tools\UI\API\Requests\GetDataSynchronizeSchemaRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetDataSynchronizeSchemaController extends ApiController
{
    public function __invoke(
        GetDataSynchronizeSchemaRequest $request,
        DataSynchronizeRegistry $registry,
        string $type
    ): JsonResponse {
        if (! $registry->hasType($type)) {
            return response()->json(['message' => "Unknown type: {$type}"], 404);
        }

        $exporter = $registry->makeExporter($type);
        $importer = $registry->makeImporter($type);

        $formats = config('data-synchronize.formats', ['csv', 'xlsx']);
        $transKey = "data-synchronize.types.{$type}";

        // Resolve label with i18n fallback
        $label = __("{$transKey}.label") !== "{$transKey}.label"
            ? __("{$transKey}.label")
            : $exporter->label();

        // Build import columns with i18n rules descriptions
        $importColumns = array_map(
            static fn (ImportColumn $col) => [
                'key' => $col->getName(),
                'label' => __("data-synchronize.columns.{$col->getName()}") !== "data-synchronize.columns.{$col->getName()}"
                    ? __("data-synchronize.columns.{$col->getName()}")
                    : $col->getLabel(),
                'required' => in_array('required', $col->getRules(), true),
                'rule_description' => $col->getRuleDescription(),
            ],
            $importer->columns()
        );

        // Build export columns with i18n
        $exportColumns = array_map(
            static fn (ExportColumn $col) => [
                'key' => $col->getName(),
                'label' => __("data-synchronize.columns.{$col->getName()}") !== "data-synchronize.columns.{$col->getName()}"
                    ? __("data-synchronize.columns.{$col->getName()}")
                    : $col->getLabel(),
            ],
            $exporter->columns()
        );

        // Build example data table
        $exampleHeaders = array_map(
            static fn (ImportColumn $col) => __("data-synchronize.columns.{$col->getName()}") !== "data-synchronize.columns.{$col->getName()}"
                ? __("data-synchronize.columns.{$col->getName()}")
                : $col->getLabel(),
            $importer->columns()
        );

        $exampleRows = [];
        foreach ($importer->examples() as $row) {
            $mapped = [];
            foreach ($importer->columns() as $column) {
                $mapped[$column->getName()] = $row[$column->getName()] ?? null;
            }
            $exampleRows[] = $mapped;
        }

        // Build filters with i18n labels
        $filters = array_map(static function (array $filter): array {
            $transKey = "data-synchronize.filters.{$filter['key']}";
            if (__($transKey) !== $transKey) {
                $filter['label'] = __($transKey);
            }
            if (isset($filter['placeholder'])) {
                $placeholderKey = "data-synchronize.filters.{$filter['key']}_placeholder";
                if (__($placeholderKey) !== $placeholderKey) {
                    $filter['placeholder'] = __($placeholderKey);
                }
            }
            return $filter;
        }, $exporter->getFilterSchema());

        return response()->json([
            'data' => [
                'type' => $type,
                'label' => $label,
                'export' => [
                    'description' => __("{$transKey}.export_description"),
                    'total' => $exporter->getTotal(),
                    'columns' => $exportColumns,
                    'filters' => $filters,
                    'formats' => $formats,
                ],
                'import' => [
                    'description' => __("{$transKey}.import_description"),
                    'chunk_size' => $importer->chunkSize(),
                    'columns' => $importColumns,
                    'examples' => [
                        'headers' => $exampleHeaders,
                        'rows' => $exampleRows,
                    ],
                    'formats' => $formats,
                ],
            ],
        ]);
    }
}
