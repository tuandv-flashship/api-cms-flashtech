<?php

namespace App\Containers\AppSection\Setting\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class GetAppearanceSettingsRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }
}
