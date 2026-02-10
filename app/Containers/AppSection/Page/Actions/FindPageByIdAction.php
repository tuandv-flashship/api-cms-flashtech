<?php

namespace App\Containers\AppSection\Page\Actions;

use App\Containers\AppSection\Page\Models\Page;
use App\Containers\AppSection\Page\Tasks\FindPageTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class FindPageByIdAction extends ParentAction
{
    public function __construct(
        private readonly FindPageTask $findPageTask,
    ) {
    }

    public function run(int $id, ?string $include = null): Page
    {
        return $this->findPageTask->run($id, $include);
    }
}
