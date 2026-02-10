<?php

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class DeleteFieldGroupRequest extends ParentRequest
{
    protected array $decode = [
        'field_group_id',
    ];
    
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('custom-fields.destroy') ?? false;
    }
}
