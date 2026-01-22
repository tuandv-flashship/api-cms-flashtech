<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Translation\Actions\GetTranslationGroupAction;
use App\Containers\AppSection\Translation\UI\API\Requests\GetTranslationGroupRequest;
use App\Containers\AppSection\Translation\UI\API\Transformers\TranslationGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetTranslationGroupController extends ApiController
{
    public function __invoke(GetTranslationGroupRequest $request, GetTranslationGroupAction $action): JsonResponse
    {
        $locale = (string) $request->route('locale');
        $group = (string) $request->input('group');

        $translations = $action->run($locale, $group);

        $payload = (object) [
            'locale' => $locale,
            'group' => $group,
            'translations' => $translations,
        ];

        return Response::create()
            ->item($payload, TranslationGroupTransformer::class)
            ->ok();
    }
}
