<?php

namespace App\Containers\AppSection\Page\Tasks;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Page\Data\Criteria\PageListFiltersCriteria;
use App\Containers\AppSection\Page\Data\Repositories\PageRepository;
use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Supports\RequestIncludes;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPagesTask extends ParentTask
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function run(array $filters): LengthAwarePaginator
    {
        $with = LanguageAdvancedManager::withTranslations(['slugable'], Page::class);
        if (RequestIncludes::has($filters['include'] ?? null, 'user')) {
            $with[] = 'user';
        }

        $repository = $this->pageRepository->resetCriteria()->resetScope();

        $repository->scope(static fn ($query) => $query->with($with));
        $repository->pushCriteria(new PageListFiltersCriteria($filters));
        $repository->addRequestCriteria(['id', 'user_id']);

        return $repository->paginate();
    }
}
