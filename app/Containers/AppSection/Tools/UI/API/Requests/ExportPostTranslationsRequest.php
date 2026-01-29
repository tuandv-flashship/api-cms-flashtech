<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Containers\AppSection\Blog\Models\Post;
use Illuminate\Validation\Rule;

final class ExportPostTranslationsRequest extends BaseDataSynchronizeExportRequest
{
    protected string $permission = 'post-translations.export';

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'class' => ['nullable', 'string', Rule::in([Post::class])],
        ]);
    }
}
