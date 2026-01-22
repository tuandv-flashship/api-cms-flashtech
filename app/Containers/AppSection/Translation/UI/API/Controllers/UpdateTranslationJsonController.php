<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Translation\Actions\UpdateTranslationGroupAction;
use App\Containers\AppSection\Translation\UI\API\Requests\UpdateTranslationJsonRequest;
use App\Containers\AppSection\Translation\UI\API\Transformers\TranslationGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateTranslationJsonController extends ApiController
{
    public function __invoke(UpdateTranslationJsonRequest $request, UpdateTranslationGroupAction $action): JsonResponse
    {
        $locale = (string) $request->route('locale');
        $translations = (array) $request->input('translations', []);

        $updated = $action->run($locale, 'json', $translations);

        $payload = (object) [
            'locale' => $locale,
            'group' => 'json',
            'translations' => $updated,
        ];

        return Response::create()
            ->item($payload, TranslationGroupTransformer::class)
            ->ok();
    }
}
