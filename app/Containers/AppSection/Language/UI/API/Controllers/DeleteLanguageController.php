<?php

namespace App\Containers\AppSection\Language\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Language\Actions\DeleteLanguageAction;
use App\Containers\AppSection\Language\UI\API\Requests\DeleteLanguageRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeleteLanguageController extends ApiController
{
    public function __invoke(DeleteLanguageRequest $request, DeleteLanguageAction $action): JsonResponse
    {
        $action->run($request->language_id);

        return Response::noContent();
    }
}
