<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use Illuminate\Validation\Rule;

final class ExportPagesRequest extends BaseDataSynchronizeExportRequest
{
    protected string $permission = 'pages.export';

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(array_map(static fn (ContentStatus $status) => $status->value, ContentStatus::cases()))],
            'template' => ['nullable', 'string', 'max:60'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);
    }
}
