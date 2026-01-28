<?php

namespace App\Containers\AppSection\Blog\Actions;

use App\Containers\AppSection\Blog\Tasks\GetBlogReportTask;
use App\Ship\Parents\Actions\Action as ParentAction;

final class GetBlogReportAction extends ParentAction
{
    public function __construct(private readonly GetBlogReportTask $getBlogReportTask)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function run(): array
    {
        return $this->getBlogReportTask->run();
    }
}
