<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Translation\Actions\ListTranslationLocalesAction;
use App\Containers\AppSection\Translation\UI\API\Requests\ListTranslationLocalesRequest;
use App\Containers\AppSection\Translation\UI\API\Transformers\TranslationLocalesTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListTranslationLocalesController extends ApiController
{
    public function __invoke(ListTranslationLocalesRequest $request, ListTranslationLocalesAction $action): JsonResponse
    {
        $payload = (object) $action->run();

        return Response::create()
            ->item($payload, TranslationLocalesTransformer::class)
            ->ok();
    }
}
