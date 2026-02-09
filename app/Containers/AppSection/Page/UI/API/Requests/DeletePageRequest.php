<?php

namespace App\Containers\AppSection\Page\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class DeletePageRequest extends ParentRequest
{
    protected array $decode = [
        'page_id',
    ];
    
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('pages.destroy') ?? false;
    }
}
