<?php

namespace App\Containers\AppSection\Language\Tasks;

use App\Containers\AppSection\Language\Data\Collections\LanguageCollection;
use App\Containers\AppSection\Language\Data\Repositories\LanguageRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListLanguagesTask extends ParentTask
{
    public function __construct(
        private readonly LanguageRepository $repository,
    ) {
    }

    public function run(): LengthAwarePaginator|LanguageCollection
    {
        return $this->repository
            ->scope(fn ($query) => $query->orderBy('lang_order'))
            ->addRequestCriteria()
            ->paginate();
    }
}
