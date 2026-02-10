<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class DeleteTagRequest extends ParentRequest
{
    protected array $decode = [
        'tag_id',
    ];
    
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('tags.destroy') ?? false;
    }
}
