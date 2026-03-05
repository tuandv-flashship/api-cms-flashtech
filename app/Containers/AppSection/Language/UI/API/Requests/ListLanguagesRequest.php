<?php

namespace App\Containers\AppSection\Language\UI\API\Requests;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Requests\Request as ParentRequest;

final class ListLanguagesRequest extends ParentRequest
{
    protected array $decode = [];
    
    
    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        // Allow public access (ListLanguages.v1.public.php has no auth middleware)
        // When authenticated, check permission
        return ! $this->user() || $this->user()->can('viewAny', Language::class);
    }
}
