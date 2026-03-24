<?php

namespace App\Containers\AppSection\Table\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class FormMetaRequest extends ParentRequest
{
    protected array $access = [
        'permissions' => '',
        'roles' => '',
    ];

    public function rules(): array
    {
        return [
            'model'  => ['required', 'string', 'max:50'],
            'action' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function authorize(): bool
    {
        // Permission check is handled in GetFormMetaAction.
        return true;
    }
}
