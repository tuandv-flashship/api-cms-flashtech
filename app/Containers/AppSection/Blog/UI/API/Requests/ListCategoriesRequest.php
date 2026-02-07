<?php

namespace App\Containers\AppSection\Blog\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListCategoriesRequest extends ParentRequest
{
    protected function prepareForValidation(): void
    {
        $parentId = $this->input('parent_id');
        
        if (!is_null($parentId)) {
            // Case 1: parent_id = 0 (plain integer for root)
            if ($parentId == 0) {
                 // Do nothing, keep it as 0
            } else {
                 // Case 2: potential Hash ID
                 // We manually decode it because we removed 'parent_id' from $decode array
                 // to prevent auto-decoding logic from stripping '0' or failing.
                 
                 // Simpler: use Hashids facade directly if available or standard apiato function
                 // But wait, standard logic in Request::input is: hashids()->decode($value)
                 // This returns an ARRAY.
                 
                 $decodedArray = \Vinkla\Hashids\Facades\Hashids::decode($parentId);
                 if (!empty($decodedArray)) {
                     $this->merge(['parent_id' => $decodedArray[0]]);
                 }
                 // If empty, it means it's not a valid hash. 
                 // It could be a raw ID (if we allow debugging) or just invalid.
                 // We leave it as is, and validation 'exists' will probably fail if it's not found.
            }
        }
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(ContentStatus::class)],
            'parent_id' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:255'],
            'order_by' => ['nullable', Rule::in(['id', 'name', 'order', 'created_at', 'updated_at'])],
            'order' => ['nullable', Rule::in(['asc', 'desc'])],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('categories.index') ?? false;
    }
}
