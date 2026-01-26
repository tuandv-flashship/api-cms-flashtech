<?php

namespace App\Containers\AppSection\Slug\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class CreateSlugRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [
            'value' => ['required', 'string'],
            'slug_id' => ['nullable', 'string'],
            'model' => ['nullable', 'string'],
            'lang_code' => ['nullable', 'string', 'max:20'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
