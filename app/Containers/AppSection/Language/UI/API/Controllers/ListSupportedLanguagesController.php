<?php

namespace App\Containers\AppSection\Language\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Language\Actions\ListSupportedLanguagesAction;
use App\Containers\AppSection\Language\UI\API\Requests\ListSupportedLanguagesRequest;
use App\Containers\AppSection\Language\UI\API\Transformers\AvailableLanguageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListSupportedLanguagesController extends ApiController
{
    public function __invoke(ListSupportedLanguagesRequest $request, ListSupportedLanguagesAction $action): JsonResponse
    {
        $languages = $action->run();

        return Response::create($languages, AvailableLanguageTransformer::class)->ok();
    }
}
