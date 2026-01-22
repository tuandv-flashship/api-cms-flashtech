<?php

namespace App\Containers\AppSection\Authorization\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Authorization\Actions\RunSystemCommandAction;
use App\Containers\AppSection\Authorization\UI\API\Requests\RunSystemCommandRequest;
use App\Containers\AppSection\Authorization\UI\API\Transformers\SystemCommandResultTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class RunSystemCommandController extends ApiController
{
    public function __invoke(RunSystemCommandRequest $request, RunSystemCommandAction $action): JsonResponse
    {
        $user = $request->user();
        $context = [
            'user_id' => $user?->getKey(),
            'user_email' => $user?->email,
            'ip' => $request->ip(),
        ];

        $result = (object) $action->run($request->input('action'), $context);

        return Response::create()
            ->item($result, SystemCommandResultTransformer::class)
            ->ok();
    }
}
