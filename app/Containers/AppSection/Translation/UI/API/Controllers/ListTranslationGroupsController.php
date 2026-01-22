<?php

namespace App\Containers\AppSection\Translation\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Translation\Actions\ListTranslationGroupsAction;
use App\Containers\AppSection\Translation\UI\API\Requests\ListTranslationGroupsRequest;
use App\Containers\AppSection\Translation\UI\API\Transformers\TranslationGroupListTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListTranslationGroupsController extends ApiController
{
    public function __invoke(ListTranslationGroupsRequest $request, ListTranslationGroupsAction $action): JsonResponse
    {
        $locale = (string) $request->route('locale');
        $groups = $action->run($locale);

        $payload = (object) [
            'locale' => $locale,
            'groups' => $groups,
        ];

        return Response::create()
            ->item($payload, TranslationGroupListTransformer::class)
            ->ok();
    }
}
