<?php

namespace App\Containers\AppSection\Gallery\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class DeleteGalleryRequest extends ParentRequest
{
    protected array $decode = [
        'gallery_id',
    ];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('galleries.destroy') ?? false;
    }
}
