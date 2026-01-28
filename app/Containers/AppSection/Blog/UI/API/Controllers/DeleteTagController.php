<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\DeleteTagAction;
use App\Containers\AppSection\Blog\UI\API\Requests\DeleteTagRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeleteTagController extends ApiController
{
    public function __invoke(DeleteTagRequest $request, DeleteTagAction $action): JsonResponse
    {
        $action->run($request->tag_id);

        return Response::noContent();
    }
}
