<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class RecordPostViewRequest extends ParentRequest
{
    protected array $decode = [
        'post_id',
    ];
    protected function prepareForValidation(): void
    {
        $this->merge([
            'post_id' => $this->route('post_id'),
        ]);
    }

    public function rules(): array
    {
        return [
            'post_id' => ['required', 'integer', 'exists:posts,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
