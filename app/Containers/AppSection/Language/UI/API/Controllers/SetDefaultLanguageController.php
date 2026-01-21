<?php

namespace App\Containers\AppSection\Language\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Language\Actions\SetDefaultLanguageAction;
use App\Containers\AppSection\Language\UI\API\Requests\SetDefaultLanguageRequest;
use App\Containers\AppSection\Language\UI\API\Transformers\LanguageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class SetDefaultLanguageController extends ApiController
{
    public function __invoke(SetDefaultLanguageRequest $request, SetDefaultLanguageAction $action): JsonResponse
    {
        $language = $action->run($request->language_id);

        return Response::create($language, LanguageTransformer::class)->ok();
    }
}
