<?php

namespace App\Containers\AppSection\RequestLog\Actions;

use App\Containers\AppSection\RequestLog\Tasks\GetRequestLogWidgetTask;
use App\Ship\Parents\Actions\Action as ParentAction;
use Illuminate\Pagination\LengthAwarePaginator;

final class GetRequestLogWidgetAction extends ParentAction
{
    public function __construct(private readonly GetRequestLogWidgetTask $getRequestLogWidgetTask)
    {
    }

    public function run(int $page = 1, int $perPage = 10): LengthAwarePaginator
    {
        return $this->getRequestLogWidgetTask->run($page, $perPage);
    }
}
