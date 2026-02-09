<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\CreateTagAction;
use App\Containers\AppSection\Blog\UI\API\Requests\CreateTagRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\TagTransformer;
use App\Containers\AppSection\Blog\UI\API\Transporters\CreateTagTransporter;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class CreateTagController extends ApiController
{
    public function __invoke(CreateTagRequest $request, CreateTagAction $action): JsonResponse
    {
        $transporter = CreateTagTransporter::fromRequest($request);

        $tag = $action->run($transporter);

        return Response::create($tag, TagTransformer::class)->created();
    }
}
