<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\CreatePostAction;
use App\Containers\AppSection\Blog\UI\API\Requests\CreatePostRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\PostTransformer;
use App\Containers\AppSection\Blog\UI\API\Transporters\CreatePostTransporter;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class CreatePostController extends ApiController
{
    public function __invoke(CreatePostRequest $request, CreatePostAction $action): JsonResponse
    {
        $transporter = CreatePostTransporter::fromRequest($request);
        
        $post = $action->run($transporter);

        return Response::create($post, PostTransformer::class)->created();
    }
}

