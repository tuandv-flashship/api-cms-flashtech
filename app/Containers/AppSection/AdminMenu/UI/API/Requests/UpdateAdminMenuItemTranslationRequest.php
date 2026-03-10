<?php

namespace App\Containers\AppSection\AdminMenu\UI\API\Requests;

use App\Containers\AppSection\Language\Models\Language;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class UpdateAdminMenuItemTranslationRequest extends ParentRequest
{
    protected array $decode = ['id'];

    public function rules(): array
    {
        $langCodes = Language::query()->pluck('lang_code')->all();

        return [
            'lang_code' => ['required', 'string', Rule::in($langCodes)],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:255'],
            'section' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('admin-menus.update') ?? false;
    }
}
