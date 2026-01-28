<?php

namespace App\Containers\AppSection\Gallery\Tasks;

use App\Containers\AppSection\Gallery\Data\Repositories\GalleryRepository;
use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Ship\Parents\Tasks\Task as ParentTask;

final class CreateGalleryTask extends ParentTask
{
    public function __construct(
        private readonly GalleryRepository $repository,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function run(array $data): Gallery
    {
        return $this->repository->create($data);
    }
}
