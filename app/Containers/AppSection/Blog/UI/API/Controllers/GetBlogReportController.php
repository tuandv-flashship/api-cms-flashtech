<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\GetBlogReportAction;
use App\Containers\AppSection\Blog\UI\API\Requests\GetBlogReportRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\BlogReportTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetBlogReportController extends ApiController
{
    public function __invoke(GetBlogReportRequest $request, GetBlogReportAction $action): JsonResponse
    {
        $payload = (object) $action->run();

        return Response::create()
            ->item($payload, BlogReportTransformer::class)
            ->ok();
    }
}
