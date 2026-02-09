<?php

namespace App\Containers\AppSection\Gallery\Tasks;

use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListGalleriesTask extends ParentTask
{
    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, (int) ($filters['limit'] ?? config('repository.pagination.limit', 10)));
        $page = max(1, (int) ($filters['page'] ?? 1));

        $with = LanguageAdvancedManager::withTranslations(['slugable'], Gallery::class);

        $query = Gallery::query()
            ->with($with)
            ->when(isset($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['is_featured']), function (Builder $query) use ($filters): void {
                $query->where('is_featured', (bool) $filters['is_featured']);
            })
            ->when(isset($filters['author_id']), function (Builder $query) use ($filters): void {
                $query->where('author_id', (int) $filters['author_id']);
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
