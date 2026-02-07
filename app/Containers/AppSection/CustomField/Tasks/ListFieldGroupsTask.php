<?php

namespace App\Containers\AppSection\CustomField\Tasks;

use App\Containers\AppSection\CustomField\Models\FieldGroup;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class ListFieldGroupsTask extends ParentTask
{
    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, (int) request()->input('limit', config('repository.pagination.limit', 10)));
        $page = max(1, (int) request()->input('page', 1));

        $orderBy = $filters['order_by'] ?? 'order';
        $order = $filters['order'] ?? 'asc';

        return FieldGroup::query()
            ->when(isset($filters['status']), function (Builder $query) use ($filters): void {
                $query->where('status', $filters['status']);
            })
            ->orderBy($orderBy, $order)
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
