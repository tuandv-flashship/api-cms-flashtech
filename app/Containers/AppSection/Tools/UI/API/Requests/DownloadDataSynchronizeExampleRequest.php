<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Containers\AppSection\Tools\Supports\DataSynchronizeRegistry;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class DownloadDataSynchronizeExampleRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [
            'format' => ['required', 'string', Rule::in(config('data-synchronize.formats', ['csv', 'xlsx']))],
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
