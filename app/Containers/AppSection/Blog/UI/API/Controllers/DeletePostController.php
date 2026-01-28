<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\DeletePostAction;
use App\Containers\AppSection\Blog\UI\API\Requests\DeletePostRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeletePostController extends ApiController
{
    public function __invoke(DeletePostRequest $request, DeletePostAction $action): JsonResponse
    {
        $action->run($request->post_id);

        return Response::noContent();
    }
}
