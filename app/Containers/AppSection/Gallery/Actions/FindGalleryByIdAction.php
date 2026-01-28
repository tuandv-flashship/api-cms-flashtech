<?php

namespace App\Containers\AppSection\Gallery\Actions;

use App\Containers\AppSection\Gallery\Models\Gallery;
use App\Containers\AppSection\Gallery\Tasks\FindGalleryTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class FindGalleryByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindGalleryTask $findGalleryTask,
    ) {
    }

    public function run(int $id): Gallery
    {
        return $this->findGalleryTask->run($id, ['slugable', 'meta']);
    }
}
