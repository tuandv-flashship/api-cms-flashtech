<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Ship\Parents\Requests\Request as ParentRequest;

final class ImportDataSynchronizeRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [
            'file_name' => ['required', 'string'],
            'offset' => ['required', 'integer', 'min:0'],
            'limit' => ['required', 'integer', 'min:1'],
            'total' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function authorize(): bool
    {
        $type = $this->route('type');
        $registry = app(DataSynchronizeRegistry::class);

        if (! $registry->hasType($type)) {
            return false;
        }

        $permission = $registry->getImportPermission($type);

        return $this->user()?->can($permission) ?? false;
    }
}
