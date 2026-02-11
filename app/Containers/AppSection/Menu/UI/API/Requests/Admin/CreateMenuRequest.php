<?php

namespace App\Containers\AppSection\Menu\UI\API\Requests\Admin;

use App\Containers\AppSection\Menu\UI\API\Requests\Admin\Concerns\NormalizesMenuNodesInput;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

final class CreateMenuRequest extends ParentRequest
{
    use NormalizesMenuNodesInput;

    protected function prepareForValidation(): void
    {
        if ($this->has('nodes') && is_array($this->input('nodes'))) {
            $this->merge([
                'nodes' => $this->normalizeNodes($this->input('nodes')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', 'unique:menus,slug'],
            'status' => ['nullable', 'string', Rule::in(['published', 'draft'])],
            'locations' => ['nullable', 'array'],
            'locations.*' => ['string', Rule::in(array_keys((array) config('menu.locations', [])))],
            'nodes' => ['nullable', 'array'],
            'nodes.*.id' => ['sometimes', 'integer'],
            'nodes.*.parent_id' => ['sometimes', 'nullable', 'integer'],
            'nodes.*.reference_type' => ['sometimes', 'nullable', 'string', 'max:180'],
            'nodes.*.reference_id' => ['sometimes', 'nullable', 'integer'],
            'nodes.*.url' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nodes.*.title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'nodes.*.url_source' => ['sometimes', 'string', Rule::in(['custom', 'resolved'])],
            'nodes.*.title_source' => ['sometimes', 'string', Rule::in(['custom', 'resolved'])],
            'nodes.*.icon_font' => ['sometimes', 'nullable', 'string', 'max:120'],
            'nodes.*.css_class' => ['sometimes', 'nullable', 'string', 'max:120'],
            'nodes.*.target' => ['sometimes', 'nullable', 'string', 'max:20'],
            'nodes.*.children' => ['sometimes', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()?->can('menus.create') ?? false;
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $nodes = $this->input('nodes');
            if (! is_array($nodes) || $nodes === []) {
                return;
            }

            $flatNodes = $this->flattenNodes($nodes);
            $ids = collect($flatNodes)
                ->pluck('id')
                ->filter(static fn (mixed $id): bool => is_int($id))
                ->values()
                ->all();

            if ($ids === []) {
                return;
            }

            if (count($ids) !== count(array_unique($ids))) {
                $validator->errors()->add('nodes', 'Duplicate node IDs are not allowed in payload.');
            }

            if ($this->hasNodeLoopInTree($nodes) || $this->hasParentIdLoop($nodes)) {
                $validator->errors()->add('nodes', 'Parent-child loop is not allowed.');
            }
        });
    }
}
