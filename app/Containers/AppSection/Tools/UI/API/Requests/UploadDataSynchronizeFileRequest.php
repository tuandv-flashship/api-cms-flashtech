<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Ship\Parents\Requests\Request as ParentRequest;

final class UploadDataSynchronizeFileRequest extends ParentRequest
{
    protected array $decode = [];
    
    
    public function rules(): array
    {
        $maxSize = (int) config('data-synchronize.max_file_size_kb', 1024);
        $mimes = implode(',', (array) config('data-synchronize.mime_types', []));

        return [
            'file' => ['required', 'file', 'max:' . $maxSize, 'mimetypes:' . $mimes],
        ];
    }

    public function authorize(): bool
    {
        $user = $this->user();

        return $user
            ? ($user->can('posts.import')
                || $user->can('post-translations.import')
                || $user->can('other-translations.import'))
            : false;
    }
}
