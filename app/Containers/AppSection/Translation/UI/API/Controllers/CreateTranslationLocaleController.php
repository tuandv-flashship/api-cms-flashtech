<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Translation\Actions\CreateTranslationLocaleAction;
use App\Containers\AppSection\Translation\UI\API\Requests\CreateTranslationLocaleRequest;
use App\Containers\AppSection\Translation\UI\API\Transformers\TranslationLocaleStatusTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class CreateTranslationLocaleController extends ApiController
{
    public function __invoke(CreateTranslationLocaleRequest $request, CreateTranslationLocaleAction $action): JsonResponse
    {
        $locale = str_replace('-', '_', (string) $request->input('locale'));
        $source = (string) $request->input('source', 'github');
        $includeVendor = (bool) $request->input('include_vendor', config('appSection-translation.include_vendor', true));

        $payload = (object) $action->run($locale, $source, $includeVendor);

        return Response::create()
            ->item($payload, TranslationLocaleStatusTransformer::class)
            ->ok();
    }
}
