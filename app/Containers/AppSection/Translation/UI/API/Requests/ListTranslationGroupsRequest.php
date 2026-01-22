<?php

namespace App\Containers\AppSection\Translation\UI\API\Requests;

use App\Containers\AppSection\Translation\Models\Translation;
use App\Ship\Parents\Requests\Request as ParentRequest;

final class ListTranslationGroupsRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9_-]+$/'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Translation::class);
    }
}
