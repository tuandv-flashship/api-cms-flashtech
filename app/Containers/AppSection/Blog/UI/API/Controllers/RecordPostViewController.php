<?php

namespace App\Containers\AppSection\Blog\UI\API\Controllers;

use Apiato\Support\Facades\Response;
use App\Containers\AppSection\Blog\Actions\RecordPostViewAction;
use App\Containers\AppSection\Blog\UI\API\Requests\RecordPostViewRequest;
use App\Containers\AppSection\Blog\UI\API\Transformers\PostViewTransformer;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class RecordPostViewController extends ApiController
{
    public function __invoke(RecordPostViewRequest $request, RecordPostViewAction $action): JsonResponse
    {
        $data = $request->validated();
        $userId = $request->user()?->getAuthIdentifier();
        $sessionId = $request->hasSession() ? $request->session()->getId() : null;

        $payload = (object) $action->run(
            (int) $data['post_id'],
            $userId !== null ? (string) $userId : null,
            $sessionId !== '' ? $sessionId : null,
            $request->ip(),
            $request->userAgent()
        );

        return Response::create()
            ->item($payload, PostViewTransformer::class)
            ->ok();
    }
}
