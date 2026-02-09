<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Supports\RequestIncludes;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListCategoriesTask extends ParentTask
{
    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, (int) ($filters['limit'] ?? config('repository.pagination.limit', 10)));
        $page = max(1, (int) ($filters['page'] ?? 1));

        $with = LanguageAdvancedManager::withTranslations(
            ['slugable', 'parent'],
            Category::class
        );

        if (RequestIncludes::has($filters['include'] ?? null, 'children')) {
            $with[] = 'children.slugable';
            $with[] = 'children';
        }

        $query = Category::query()
            ->with($with)
            ->when(isset($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['parent_id']), function (Builder $query) use ($filters): void {
                $query->where('parent_id', (int) $filters['parent_id']);
            })
            ->when(isset($filters['is_featured']), function (Builder $query) use ($filters): void {
                $query->where('is_featured', (bool) $filters['is_featured']);
            })
            ->when(isset($filters['is_default']), function (Builder $query) use ($filters): void {
                $query->where('is_default', (bool) $filters['is_default']);
            })
            ->when(! empty($filters['search']), function (Builder $query) use ($filters): void {
                $search = (string) $filters['search'];
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            });

        $orderBy = $filters['order_by'] ?? 'order';
        $order = $filters['order'] ?? 'asc';

        return $query
            ->orderBy($orderBy, $order)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
