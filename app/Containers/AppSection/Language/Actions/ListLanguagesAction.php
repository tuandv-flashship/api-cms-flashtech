<?php

namespace App\Containers\AppSection\Language\Actions;

use App\Containers\AppSection\Language\Data\Collections\LanguageCollection;
use App\Containers\AppSection\Language\Tasks\ListLanguagesTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListLanguagesAction extends ParentAction
{
    public function __construct(
        private readonly ListLanguagesTask $listLanguagesTask,
    ) {
    }

    public function run(): LengthAwarePaginator|LanguageCollection
    {
        return $this->listLanguagesTask->run();
    }
}
