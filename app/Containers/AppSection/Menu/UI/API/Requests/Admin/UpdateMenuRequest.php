<?php

namespace App\Containers\AppSection\Menu\UI\API\Requests\Admin;

use App\Containers\AppSection\Menu\Models\MenuNode;
use App\Containers\AppSection\Menu\UI\API\Requests\Admin\Concerns\NormalizesMenuNodesInput;
use App\Ship\Parents\Requests\Request as ParentRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

final class UpdateMenuRequest extends ParentRequest
{
    use NormalizesMenuNodesInput;

    protected array $decode = [
        'id',
    ];

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
        $id = (int) $this->id;

        return [
            'name' => ['sometimes', 'string', 'max:120'],
            'slug' => [
                'sometimes',
                'string',
                'max:120',
                'alpha_dash',
                Rule::unique('menus', 'slug')->ignore($id),
            ],
            'status' => ['sometimes', 'string', Rule::in(['published', 'draft'])],
            'locations' => ['sometimes', 'array'],
            'locations.*' => ['string', Rule::in(array_keys((array) config('menu.locations', [])))],
            'nodes' => ['sometimes', 'array'],
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
        return $this->user()?->can('menus.update') ?? false;
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
                return;
            }

            $menuId = (int) $this->id;
            $existingIds = MenuNode::query()
                ->where('menu_id', $menuId)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->map(static fn (mixed $id): int => (int) $id)
                ->all();

            if (count($existingIds) !== count($ids)) {
                $validator->errors()->add('nodes', 'One or more node IDs do not belong to this menu.');
            }

            if ($this->hasNodeLoopInTree($nodes) || $this->hasParentIdLoop($nodes)) {
                $validator->errors()->add('nodes', 'Parent-child loop is not allowed.');
            }
        });
    }
}
