<?php

namespace App\Containers\AppSection\Gallery\Tasks;

use App\Containers\AppSection\Gallery\Data\Repositories\GalleryRepository;
use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\LanguageAdvanced\Supports\LanguageAdvancedManager;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListGalleriesTask extends ParentTask
{
    public function __construct(
        private readonly GalleryRepository $repository,
    ) {
    }

    public function run(): LengthAwarePaginator
    {
        $with = LanguageAdvancedManager::withTranslations(['slugable'], Gallery::class);

        return $this->repository
            ->scope(static fn ($query) => $query->with($with))
            ->addRequestCriteria()
            ->paginate();
    }
}
