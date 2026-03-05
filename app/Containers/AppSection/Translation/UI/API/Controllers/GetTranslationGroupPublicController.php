<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use App\Containers\AppSection\Translation\Models\Translation;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetTranslationGroupPublicController extends ApiController
{
    public function __invoke(string $locale, string $group): JsonResponse
    {
        $translations = Translation::getForGroup($locale, $group);

        $response = response()->json([
            'data' => $translations,
            'meta' => [
                'locale' => $locale,
                'group' => $group,
                'total_keys' => count($translations),
            ],
        ]);

        $etag = md5(json_encode($translations));

        return $response
            ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=60')
            ->setEtag($etag);
    }
}
