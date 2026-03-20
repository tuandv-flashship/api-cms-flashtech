<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use App\Containers\AppSection\Translation\Actions\GetTranslationGroupAction;
use App\Containers\AppSection\Translation\UI\API\Requests\GetTranslationGroupRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetTranslationGroupController extends ApiController
{
    public function __invoke(GetTranslationGroupRequest $request, GetTranslationGroupAction $action): JsonResponse
    {
        $locale = (string) $request->route('locale');
        $group = $request->input('group') ?: null;
        $search = $request->input('search') ?: null;
        $page = (int) ($request->input('page', 1));
        $limit = (int) ($request->input('limit', config('repository.pagination.limit', 10)));

        $result = $action->run($locale, $group, $search, $page, $limit);

        return response()->json([
            'data' => $result['items'],
            'meta' => [
                'locale' => $locale,
                'group' => $group,
                'search' => $search,
                'pagination' => [
                    'total' => $result['total'],
                    'count' => count($result['items']),
                    'per_page' => $result['per_page'],
                    'current_page' => $result['page'],
                    'total_pages' => $result['last_page'],
                ],
            ],
        ]);
    }
}
