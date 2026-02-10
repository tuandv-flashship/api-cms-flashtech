<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Data\Repositories\CategoryRepository;
use App\Containers\AppSection\Blog\Models\Category;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListCategoriesTask extends ParentTask
{
    public function __construct(
        private readonly CategoryRepository $repository,
    ) {
    }

    public function run(): LengthAwarePaginator
    {
        $with = LanguageAdvancedManager::withTranslations(
            ['slugable', 'parent'],
            Category::class
        );

        $include = request()?->input('include');
        if ($include && str_contains($include, 'children')) {
            $with[] = 'children.slugable';
            $with[] = 'children';
        }

        return $this->repository
            ->scope(static fn ($query) => $query->with($with))
            ->addRequestCriteria()
            ->paginate();
    }
}
