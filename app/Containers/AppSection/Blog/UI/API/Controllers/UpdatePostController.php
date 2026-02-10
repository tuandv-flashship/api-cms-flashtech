<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\UpdatePostAction;
use App\Containers\AppSection\Blog\UI\API\Requests\UpdatePostRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\PostTransformer;
use App\Containers\AppSection\Blog\UI\API\Transporters\UpdatePostTransporter;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdatePostController extends ApiController
{
    public function __invoke(UpdatePostRequest $request, UpdatePostAction $action): JsonResponse
    {
        $transporter = UpdatePostTransporter::fromArray([
            'post_id' => $request->post_id,
            ...$request->validated(),
        ]);

        $post = $action->run($transporter);

        return Response::create($post, PostTransformer::class)->ok();
    }
}
