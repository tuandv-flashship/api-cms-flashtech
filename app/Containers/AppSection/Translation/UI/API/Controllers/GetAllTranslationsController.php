<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use App\Containers\AppSection\Translation\Models\Translation;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetAllTranslationsController extends ApiController
{
    public function __invoke(string $locale): JsonResponse
    {
        $translations = Translation::getAllForLocale($locale);

        $response = response()->json([
            'data' => $translations,
            'meta' => [
                'locale' => $locale,
                'total_keys' => collect($translations)->flatten()->count(),
            ],
        ]);

        // HTTP cache — FE can cache for 5 min, ETag for conditional requests
        $etag = md5(json_encode($translations));

        return $response
            ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=60')
            ->setEtag($etag);
    }
}
