<?php

namespace App\Containers\AppSection\Page\Data\Criteria;

use App\Ship\Parents\Criteria\Criteria as ParentCriteria;
use Prettus\Repository\Contracts\RepositoryInterface;

final class PageListFiltersCriteria extends ParentCriteria
{
    /**
     * @param array<string, mixed> $filters
     */
    public function __construct(
        private readonly array $filters,
    ) {
    }

    public function apply($model, RepositoryInterface $repository)
    {
        return $model
            ->when(isset($this->filters['status']), function ($query): void {
                $query->where('status', $this->filters['status']);
            })
            ->when(isset($this->filters['template']), function ($query): void {
                $query->where('template', $this->filters['template']);
            });
    }
}
