<?php

namespace App\Containers\AppSection\Page\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Page\Actions\DeletePageAction;
use App\Containers\AppSection\Page\UI\API\Requests\DeletePageRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeletePageController extends ApiController
{
    public function __invoke(DeletePageRequest $request, DeletePageAction $action): JsonResponse
    {
        $action->run($request->page_id);

        return Response::noContent();
    }
}
