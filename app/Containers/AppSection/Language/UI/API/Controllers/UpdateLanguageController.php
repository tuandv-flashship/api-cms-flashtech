<?php

namespace App\Containers\AppSection\Language\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Language\Actions\UpdateLanguageAction;
use App\Containers\AppSection\Language\UI\API\Requests\UpdateLanguageRequest;
use App\Containers\AppSection\Language\UI\API\Transformers\LanguageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateLanguageController extends ApiController
{
    public function __invoke(UpdateLanguageRequest $request, UpdateLanguageAction $action): JsonResponse
    {
        $language = $action->run($request->language_id, $request->validated());

        return Response::create($language, LanguageTransformer::class)->ok();
    }
}
