<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ExportDataSynchronizeRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        $baseRules = [
            'format' => ['sometimes', 'string', Rule::in(config('data-synchronize.formats', ['csv', 'xlsx']))],
            'columns' => ['sometimes', 'array'],
            'columns.*' => ['string'],
        ];

        // Merge type-specific filter validation rules from the exporter
        $type = $this->route('type');
        if ($type) {
            $registry = app(DataSynchronizeRegistry::class);
            if ($registry->hasType($type)) {
                $exporter = $registry->makeExporter($type);
                $baseRules = array_merge($baseRules, $exporter->getFilterValidationRules());
            }
        }

        return $baseRules;
    }

    public function authorize(): bool
    {
        $type = $this->route('type');
        $registry = app(DataSynchronizeRegistry::class);

        if (! $registry->hasType($type)) {
            return false;
        }

        $permission = $registry->getExportPermission($type);

        return $this->user()?->can($permission) ?? false;
    }
}
