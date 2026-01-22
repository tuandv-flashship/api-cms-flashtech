<?php

namespace App\Containers\AppSection\Translation\UI\API\Requests;

use App\Containers\AppSection\Translation\Models\Translation;
use App\Ship\Parents\Requests\Request as ParentRequest;

final class ListTranslationLocalesRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()->can('viewAny', Translation::class);
    }
}
