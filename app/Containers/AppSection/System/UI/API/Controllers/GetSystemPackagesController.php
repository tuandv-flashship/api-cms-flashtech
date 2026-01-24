<?php

namespace App\Containers\AppSection\System\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\System\Actions\GetSystemPackagesAction;
use App\Containers\AppSection\System\UI\API\Requests\GetSystemPackagesRequest;
use App\Containers\AppSection\System\UI\API\Transformers\SystemPackageTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class GetSystemPackagesController extends ApiController
{
    public function __invoke(GetSystemPackagesRequest $request, GetSystemPackagesAction $action): JsonResponse
    {
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 15);

        $packages = $action->run($page, $perPage);

        return Response::create($packages, SystemPackageTransformer::class)->ok();
    }
}
