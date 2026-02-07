<?php

namespace App\Containers\AppSection\Page\Tasks;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListPagesTask extends ParentTask
{
    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, (int) request()->input('limit', config('repository.pagination.limit', 10)));
        $page = max(1, (int) request()->input('page', 1));

        $with = LanguageAdvancedManager::withTranslations(['slugable', 'user'], Page::class);

        $query = Page::query()
            ->with($with)
            ->when(isset($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->when(isset($filters['template']), function (Builder $query) use ($filters): void {
                $query->where('template', $filters['template']);
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
