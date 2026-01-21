<?php

namespace App\Containers\AppSection\Language\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Language\Actions\ListAvailableLanguagesAction;
use App\Containers\AppSection\Language\UI\API\Requests\ListAvailableLanguagesRequest;
use App\Containers\AppSection\Language\UI\API\Transformers\AvailableLanguageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListAvailableLanguagesController extends ApiController
{
    public function __invoke(ListAvailableLanguagesRequest $request, ListAvailableLanguagesAction $action): JsonResponse
    {
        $languages = $action->run();

        return Response::create($languages, AvailableLanguageTransformer::class)->ok();
    }
}
