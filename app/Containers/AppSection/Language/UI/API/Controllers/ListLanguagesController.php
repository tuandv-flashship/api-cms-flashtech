<?php

namespace App\Containers\AppSection\Language\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Language\Actions\ListLanguagesAction;
use App\Containers\AppSection\Language\UI\API\Requests\ListLanguagesRequest;
use App\Containers\AppSection\Language\UI\API\Transformers\LanguageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListLanguagesController extends ApiController
{
    public function __invoke(ListLanguagesRequest $request, ListLanguagesAction $action): JsonResponse
    {
        $languages = $action->run();

        return Response::create($languages, LanguageTransformer::class)->ok();
    }
}
