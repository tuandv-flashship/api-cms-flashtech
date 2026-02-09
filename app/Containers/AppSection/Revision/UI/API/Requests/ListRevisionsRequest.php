<?php

namespace App\Containers\AppSection\Revision\UI\API\Requests;

use App\Containers\AppSection\Revision\Supports\RevisionableResolver;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class ListRevisionsRequest extends ParentRequest
{
    protected array $decode = ['revisionable_id'];
    
    
    public function rules(): array
    {
        $supportedTypes = (new RevisionableResolver())->supportedTypes();
        $typeRules = ['required', 'string'];
        if ($supportedTypes !== []) {
            $typeRules[] = Rule::in($supportedTypes);
        }

        return [
            'type' => $typeRules,
            'revisionable_id' => ['required', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:200'],
            'page' => ['nullable', 'integer', 'min:1'],
            'order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('revisions.index') ?? false;
    }
}
