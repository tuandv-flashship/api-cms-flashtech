<?php

namespace App\Containers\AppSection\Gallery\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class FindGalleryByIdRequest extends ParentRequest
{
    protected array $decode = [
        'gallery_id',
    ];
    
    public function rules(): array
    {
        return [
            'include' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('galleries.index') ?? false;
    }
}
