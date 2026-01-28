<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Models\Category;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

final class ListCategoriesTreeTask extends ParentTask
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function run(?string $status = null): array
    {
        $categories = Category::query()
            ->with('slugable')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $grouped = $categories->groupBy('parent_id');

        return $this->buildTree((int) 0, $grouped);
    }

    /**
     * @param \Illuminate\Support\Collection<int, Collection> $grouped
     * @return array<int, array<string, mixed>>
     */
    private function buildTree(int $parentId, $grouped): array
    {
        if (! $grouped->has($parentId)) {
            return [];
        }

        $items = [];
        foreach ($grouped->get($parentId) as $category) {
            $children = $this->buildTree((int) $category->getKey(), $grouped);

            $items[] = [
                'id' => $category->getHashedKey(),
                'name' => $category->name,
                'slug' => $category->slug,
                'parent_id' => $this->hashId($category->parent_id),
                'children' => $children,
                'has_children' => count($children) > 0,
            ];
        }

        return $items;
    }

    private function hashId(int|string|null $id): int|string|null
    {
        if ($id === null) {
            return null;
        }

        $intId = (int) $id;
        if ($intId <= 0) {
            return $intId;
        }

        return config('apiato.hash-id') ? hashids()->encodeOrFail($intId) : $intId;
    }
}
