<?php

namespace App\Containers\AppSection\Slug\UI\API\Controllers;

use App\Containers\AppSection\Slug\Actions\CreateSlugAction;
use App\Containers\AppSection\Slug\UI\API\Requests\CreateSlugRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class CreateSlugController extends ApiController
{
    public function __invoke(CreateSlugRequest $request, CreateSlugAction $action): JsonResponse
    {
        $slugId = $request->input('slug_id');

        if (is_string($slugId)) {
            $slugId = trim($slugId);
            if ($slugId === '') {
                $slugId = null;
            } elseif (! ctype_digit($slugId) && config('apiato.hash-id')) {
                $slugId = hashids()->decodeOrFail($slugId);
            } else {
                $slugId = (int) $slugId;
            }
        }

        $slug = $action->run(
            $request->input('value'),
            $slugId,
            $request->input('model'),
            $request->input('lang_code'),
        );

        return response()->json([
            'data' => [
                'slug' => $slug,
            ],
        ]);
    }
}
