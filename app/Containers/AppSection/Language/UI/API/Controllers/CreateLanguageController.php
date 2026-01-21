<?php

namespace App\Containers\AppSection\Language\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Language\Actions\CreateLanguageAction;
use App\Containers\AppSection\Language\UI\API\Requests\CreateLanguageRequest;
use App\Containers\AppSection\Language\UI\API\Transformers\LanguageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class CreateLanguageController extends ApiController
{
    public function __invoke(CreateLanguageRequest $request, CreateLanguageAction $action): JsonResponse
    {
        $language = $action->run($request->validated());

        return Response::create($language, LanguageTransformer::class)->created();
    }
}
