<?php

namespace App\Containers\AppSection\Language\UI\API\Requests;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Requests\Request as ParentRequest;

final class SetDefaultLanguageRequest extends ParentRequest
{
    protected array $decode = [
        'language_id',
    ];
    
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', Language::class);
    }
}
