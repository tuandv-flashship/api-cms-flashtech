<?php

namespace App\Containers\AppSection\Blog\Tasks;

use App\Containers\AppSection\Blog\Data\Criteria\PostListFiltersCriteria;
use App\Containers\AppSection\Blog\Data\Repositories\PostRepository;
use App\Containers\AppSection\Blog\Models\Post;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListPostsTask extends ParentTask
{
    public function __construct(
        private readonly PostRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $relationFilters Relationship-based filters (category_ids, tag_ids)
     */
    public function run(array $relationFilters = []): LengthAwarePaginator
    {
        $with = LanguageAdvancedManager::withTranslations(
            ['categories.slugable', 'tags.slugable', 'slugable', 'galleryMeta'],
            Post::class
        );

        $include = request()?->input('include');
        if ($include && str_contains($include, 'author')) {
            $with[] = 'author';
        }

        $langCode = LanguageAdvancedManager::getTranslationLocale();
        if ($langCode && ! LanguageAdvancedManager::isDefaultLocale($langCode)) {
            $with['galleryMeta.translations'] = static fn ($query) => $query->where('lang_code', $langCode);
        }

        $repository = $this->repository;

        $repository->scope(static fn ($query) => $query->with($with));

        if (! empty($relationFilters)) {
            $repository->pushCriteria(new PostListFiltersCriteria($relationFilters));
        }

        return $repository
            ->addRequestCriteria()
            ->paginate();
    }
}
