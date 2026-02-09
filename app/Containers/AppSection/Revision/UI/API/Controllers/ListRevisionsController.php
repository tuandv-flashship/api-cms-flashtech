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
        $revisions = $action->run(
            (string) $request->input('type'),
            (int) $request->input('revisionable_id'),
            $request->input('order'),
            $request->integer('limit'),
            $request->integer('page'),
        );

        return Response::create($revisions, RevisionTransformer::class)->ok();
    }
}
