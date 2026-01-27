<?php

namespace App\Containers\AppSection\Media\UI\API\Controllers;

use App\Containers\AppSection\Media\Actions\DownloadMediaFileAction;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Containers\AppSection\Media\UI\API\Requests\DownloadMediaFileRequest;
use App\Ship\Parents\Controllers\ApiController;
use Illuminate\Http\JsonResponse;

final class DownloadMediaFileController extends ApiController
{
    public function __invoke(DownloadMediaFileRequest $request, DownloadMediaFileAction $action): JsonResponse
    {
        $file = $action->run(
            $request->input('url'),
            (int) $request->input('folder_id', 0),
            (int) $request->user()->getKey(),
            $request->input('visibility'),
            $request->input('access_mode'),
        );

        $service = app(MediaService::class);
        $signedUrl = $service->getSignedUrl($file);
        $accessMode = $service->resolveAccessModeForFile($file);

        return response()->json([
            'data' => [
                'id' => $file->getHashedKey(),
                'src' => $file->visibility === 'public' ? $service->url($file->url) : $signedUrl,
                'url' => $file->url,
                'access_mode' => $accessMode,
                'signed_url' => $signedUrl,
            ],
        ]);
    }
}
