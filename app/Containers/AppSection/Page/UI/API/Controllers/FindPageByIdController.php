<?php

namespace App\Containers\AppSection\Page\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Page\Actions\FindPageByIdAction;
use App\Containers\AppSection\Page\Supports\PageOptions;
use App\Containers\AppSection\Page\UI\API\Requests\FindPageByIdRequest;
use App\Containers\AppSection\Page\UI\API\Transformers\PageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class FindPageByIdController extends ApiController
{
    public function __invoke(FindPageByIdRequest $request, FindPageByIdAction $action): JsonResponse
    {
        $page = $action->run($request->page_id, ['slugable', 'user']);

        $response = Response::create($page, PageTransformer::class);

        if (PageOptions::shouldIncludeOptions($request->query('include'))) {
            $response->addMeta([
                'options' => PageOptions::pageOptions(),
            ]);
        }

        return $response->ok();
    }
}
