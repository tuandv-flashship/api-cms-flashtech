<?php

namespace App\Containers\AppSection\Member\UI\API\Requests\Admin;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class DeleteMemberRequest extends ParentRequest
{
    protected array $decode = [
        'id',
    ];
    
    public function rules(): array
    {
        return [
            //
        ];
    }

    public function authorize(): bool
    {
        return $this->user() && $this->user()->can('members.destroy');
    }
}
