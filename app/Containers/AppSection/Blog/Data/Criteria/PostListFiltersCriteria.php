<?php

namespace App\Containers\AppSection\Blog\Data\Criteria;

use App\Ship\Parents\Criteria\Criteria as ParentCriteria;
use Illuminate\Database\Eloquent\Builder;
use Prettus\Repository\Contracts\RepositoryInterface;

final class PostListFiltersCriteria extends ParentCriteria
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
            ->when(! empty($this->filters['category_ids']), function (Builder $query): void {
                $ids = array_filter((array) $this->filters['category_ids']);
                $query->whereHas('categories', fn (Builder $q) => $q->whereIn('categories.id', $ids));
            })
            ->when(! empty($this->filters['tag_ids']), function (Builder $query): void {
                $ids = array_filter((array) $this->filters['tag_ids']);
                $query->whereHas('tags', fn (Builder $q) => $q->whereIn('tags.id', $ids));
            });
    }
}
