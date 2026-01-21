<?php

namespace App\Containers\AppSection\Language\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Language\Actions\GetCurrentLanguageAction;
use App\Containers\AppSection\Language\UI\API\Requests\GetCurrentLanguageRequest;
use App\Containers\AppSection\Language\UI\API\Transformers\LanguageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetCurrentLanguageController extends ApiController
{
    public function __invoke(GetCurrentLanguageRequest $request, GetCurrentLanguageAction $action): JsonResponse
    {
        $language = $action->run($request->header('X-Locale'));

        return Response::create($language, LanguageTransformer::class)->ok();
    }
}
