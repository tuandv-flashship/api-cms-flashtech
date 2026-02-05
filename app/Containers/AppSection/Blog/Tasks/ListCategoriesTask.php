<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListCategoriesTask extends ParentTask
{
    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters, int $perPage, int $page): LengthAwarePaginator
    {
        $with = LanguageAdvancedManager::withTranslations(
            ['slugable', 'parent'],
            Category::class
        );

        // Check if 'children' is requested in includes to eager load it
        // Note: Task doesn't directly access Request, but we can verify usually via Apiato Repository or here if needed.
        // However, to fix N+1 securely we should add it if we know we need it.
        // A simpler way is to check the request directly or just rely on 'with' if we passed includes.
        
        // Since we are inside a Task, accessing request() helper is acceptable but coupling.
        // Better: pass includes array to Task. But for quick fix:
        $includes = request()->query('include');
        if($includes && str_contains($includes, 'children')) {
            $with[] = 'children';
             // Also load translations for children if language advanced is used, but basic children rel is key.
             // If children also have translations:
             // $with[] = 'children.translations'; // If needed. 
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
