<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Models\Post;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListPostsTask extends ParentTask
{
    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters, int $perPage, int $page): LengthAwarePaginator
    {
        $query = Post::query()
            ->with(['categories', 'tags', 'slugable', 'author'])
            ->when(isset($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['is_featured']), function (Builder $query) use ($filters): void {
                $query->where('is_featured', (bool) $filters['is_featured']);
            })
            ->when(isset($filters['author_id']), function (Builder $query) use ($filters): void {
                $query->where('author_id', (int) $filters['author_id']);
            })
            ->when(! empty($filters['category_ids']), function (Builder $query) use ($filters): void {
                $ids = array_filter((array) $filters['category_ids']);
                $query->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $ids));
            })
            ->when(! empty($filters['tag_ids']), function (Builder $query) use ($filters): void {
                $ids = array_filter((array) $filters['tag_ids']);
                $query->whereHas('tags', fn (Builder $q) => $q->whereIn('tags.id', $ids));
            })
            ->when(! empty($filters['search']), function (Builder $query) use ($filters): void {
                $search = (string) $filters['search'];
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('name', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            });

        $orderBy = $filters['order_by'] ?? 'updated_at';
        $order = $filters['order'] ?? 'desc';

        return $query
            ->orderBy($orderBy, $order)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
