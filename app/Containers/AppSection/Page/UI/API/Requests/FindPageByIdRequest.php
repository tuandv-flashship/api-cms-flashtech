<?php

namespace App\Containers\AppSection\Page\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class FindPageByIdRequest extends ParentRequest
{
    protected array $decode = [
        'page_id',
    ];
    
    public function rules(): array
    {
        return [
            'include' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages.index') ?? false;
    }
}
