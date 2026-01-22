<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Translation\Actions\UpdateTranslationGroupAction;
use App\Containers\AppSection\Translation\UI\API\Requests\UpdateTranslationGroupRequest;
use App\Containers\AppSection\Translation\UI\API\Transformers\TranslationGroupTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateTranslationGroupController extends ApiController
{
    public function __invoke(UpdateTranslationGroupRequest $request, UpdateTranslationGroupAction $action): JsonResponse
    {
        $locale = (string) $request->route('locale');
        $group = (string) $request->input('group');
        $translations = (array) $request->input('translations', []);

        $updated = $action->run($locale, $group, $translations);

        $payload = (object) [
            'locale' => $locale,
            'group' => $group,
            'translations' => $updated,
        ];

        return Response::create()
            ->item($payload, TranslationGroupTransformer::class)
            ->ok();
    }
}
