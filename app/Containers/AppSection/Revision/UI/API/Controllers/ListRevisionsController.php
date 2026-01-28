<?php

namespace App\Containers\AppSection\Revision\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Revision\Actions\ListRevisionsAction;
use App\Containers\AppSection\Revision\UI\API\Requests\ListRevisionsRequest;
use App\Containers\AppSection\Revision\UI\API\Transformers\RevisionTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class ListRevisionsController extends ApiController
{
    public function __invoke(ListRevisionsRequest $request, ListRevisionsAction $action): JsonResponse
    {
        $limit = $request->input('limit');
        $perPage = is_numeric($limit)
            ? (int) $limit
            : (int) $request->input('per_page', config('revision.default_per_page', 20));

        $revisions = $action->run(
            (string) $request->input('type'),
            (int) $request->input('revisionable_id'),
            $perPage,
            (int) $request->input('page', 1),
            $request->input('order')
        );

        return Response::create($revisions, RevisionTransformer::class)->ok();
    }
}
