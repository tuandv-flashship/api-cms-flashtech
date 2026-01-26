<?php

namespace App\Containers\AppSection\LanguageAdvanced\UI\API\Controllers;

use App\Containers\AppSection\LanguageAdvanced\Actions\UpdateSlugTranslationAction;
use App\Containers\AppSection\LanguageAdvanced\UI\API\Requests\UpdateSlugTranslationRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class UpdateSlugTranslationController extends ApiController
{
    public function __invoke(UpdateSlugTranslationRequest $request, UpdateSlugTranslationAction $action): JsonResponse
    {
        $translation = $action->run(
            (int) $request->route('slug_id'),
            $request->input('lang_code'),
            $request->input('key'),
            $request->input('model'),
        );

        return response()->json([
            'data' => [
                'slug_id' => $translation->slugs_id,
                'lang_code' => $translation->lang_code,
                'key' => $translation->key,
                'prefix' => $translation->prefix,
            ],
        ]);
    }
}
