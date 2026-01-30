<?php

namespace App\Containers\AppSection\Page\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Page\Actions\UpdatePageAction;
use App\Containers\AppSection\Page\UI\API\Requests\UpdatePageRequest;
use App\Containers\AppSection\Page\UI\API\Transformers\PageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class UpdatePageController extends ApiController
{
    public function __invoke(UpdatePageRequest $request, UpdatePageAction $action): JsonResponse
    {
        $payload = $request->validated();
        $data = Arr::only($payload, [
            'name',
            'description',
            'content',
            'status',
            'image',
            'template',
        ]);

        $page = $action->run(
            $request->page_id,
            $data,
            $payload['slug'] ?? null,
            $payload['seo_meta'] ?? null,
            $payload['custom_fields'] ?? null,
        );

        return Response::create($page, PageTransformer::class)->ok();
    }
}
