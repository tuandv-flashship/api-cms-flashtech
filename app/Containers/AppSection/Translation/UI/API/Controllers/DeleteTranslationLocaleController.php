<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Translation\Actions\DeleteTranslationLocaleAction;
use App\Containers\AppSection\Translation\UI\API\Requests\DeleteTranslationLocaleRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DeleteTranslationLocaleController extends ApiController
{
    public function __invoke(DeleteTranslationLocaleRequest $request, DeleteTranslationLocaleAction $action): JsonResponse
    {
        $locale = (string) $request->route('locale');
        $action->run($locale);

        return Response::noContent();
    }
}
