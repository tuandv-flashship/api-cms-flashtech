<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\UpdateTagAction;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdateTagRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\TagTransformer;
use App\Containers\AppSection\Blog\UI\API\Transporters\UpdateTagTransporter;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateTagController extends ApiController
{
    public function __invoke(UpdateTagRequest $request, UpdateTagAction $action): JsonResponse
    {
        $transporter = UpdateTagTransporter::fromArray([
            'tag_id' => $request->tag_id,
            ...$request->validated(),
        ]);

        $tag = $action->run($transporter);

        return Response::create($tag, TagTransformer::class)->ok();
    }
}
