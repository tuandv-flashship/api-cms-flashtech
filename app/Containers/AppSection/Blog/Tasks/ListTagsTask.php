<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Models\Tag;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListTagsTask extends ParentTask
{
    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, (int) ($filters['limit'] ?? config('repository.pagination.limit', 10)));
        $page = max(1, (int) ($filters['page'] ?? 1));

        $with = LanguageAdvancedManager::withTranslations(
            ['slugable'],
            Tag::class
        );

        $query = Tag::query()
            ->with($with)
            ->when(isset($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['search']), function (Builder $query) use ($filters): void {
                $search = (string) $filters['search'];
                $query->where('name', 'like', '%' . $search . '%');
            });

        $orderBy = $filters['order_by'] ?? 'created_at';
        $order = $filters['order'] ?? 'desc';

        return $query
            ->orderBy($orderBy, $order)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
