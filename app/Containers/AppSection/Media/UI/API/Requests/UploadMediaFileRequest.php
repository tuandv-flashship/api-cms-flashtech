<?php

namespace App\Containers\AppSection\Media\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Validation\Rule;

final class UploadMediaFileRequest extends ParentRequest
{
    protected array $decode = ['folder_id'];

    public function rules(): array
    {
        return [
            'file' => ['required', 'file'],
            'folder_id' => ['nullable', 'integer', 'min:0'],
            'visibility' => ['nullable', 'string', Rule::in(['public', 'private'])],
            'access_mode' => ['nullable', 'string', Rule::in(['auth', 'signed'])],
            'dzuuid' => ['nullable', 'string'],
            'dzchunkindex' => ['nullable', 'integer', 'min:0'],
            'dztotalchunkcount' => ['nullable', 'integer', 'min:1'],
            'dztotalfilesize' => ['nullable', 'integer', 'min:0'],
            'dzchunksize' => ['nullable', 'integer', 'min:0'],
            'filename' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('files.create') ?? false;
    }
}
