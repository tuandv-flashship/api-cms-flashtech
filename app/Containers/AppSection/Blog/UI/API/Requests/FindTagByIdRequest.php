<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class FindTagByIdRequest extends ParentRequest
{
    protected array $decode = [
        'tag_id',
    ];
    
    public function rules(): array
    {
        return [
            'include' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('tags.index') ?? false;
    }
}
