<?php

namespace App\Containers\AppSection\Page\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Page\Actions\CreatePageAction;
use App\Containers\AppSection\Page\UI\API\Requests\CreatePageRequest;
use App\Containers\AppSection\Page\UI\API\Transformers\PageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

final class CreatePageController extends ApiController
{
    public function __invoke(CreatePageRequest $request, CreatePageAction $action): JsonResponse
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
            $data,
            $payload['slug'] ?? null,
            $payload['seo_meta'] ?? null,
            $payload['custom_fields'] ?? null,
        );

        return Response::create($page, PageTransformer::class)->created();
    }
}
