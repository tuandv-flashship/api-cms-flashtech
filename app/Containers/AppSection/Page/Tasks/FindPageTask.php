<?php

namespace App\Containers\AppSection\Page\Tasks;

use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Containers\AppSection\Page\Data\Repositories\PageRepository;
use App\Containers\AppSection\Page\Models\Page;
use App\Ship\Supports\RequestIncludes;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class FindPageTask extends ParentTask
{
    public function __construct(
        private readonly PageRepository $pageRepository,
    ) {
    }

    public function run(int $id, ?string $include = null): Page
    {
        $with = LanguageAdvancedManager::withTranslations(['slugable'], Page::class);
        if (RequestIncludes::has($include, 'user')) {
            $with[] = 'user';
        }

        $repository = $this->pageRepository->resetCriteria()->resetScope();
        $repository->scope(static fn ($query) => $query->with($with));

        return $repository->findOrFail($id);
    }
}
