<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class GetDataSynchronizeSchemaRequest extends ParentRequest
{
    protected array $decode = [];

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }
}
