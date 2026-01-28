<?php

namespace App\Containers\AppSection\Gallery\Tasks;

use App\Containers\AppSection\Gallery\Data\Repositories\GalleryRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class DeleteGalleryTask extends ParentTask
{
    public function __construct(
        private readonly GalleryRepository $repository,
    ) {
    }

    public function run(int $id): bool
    {
        return (bool) $this->repository->delete($id);
    }
}
