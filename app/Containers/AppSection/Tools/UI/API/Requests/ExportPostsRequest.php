<?php

namespace App\Containers\AppSection\Tools\UI\API\Requests;

use App\Containers\AppSection\Blog\Enums\ContentStatus;
use Illuminate\Validation\Rule;

final class ExportPostsRequest extends BaseDataSynchronizeExportRequest
{
    protected string $permission = 'posts.export';

    protected array $decode = [
        'category_id',
    ];

    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'limit' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', Rule::in(array_map(static fn (ContentStatus $status) => $status->value, ContentStatus::cases()))],
            'is_featured' => ['nullable', 'boolean'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);
    }
}
