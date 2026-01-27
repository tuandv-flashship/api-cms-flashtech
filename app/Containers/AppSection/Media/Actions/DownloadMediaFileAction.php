<?php

namespace App\Containers\AppSection\Media\Actions;

use App\Containers\AppSection\Media\Models\MediaFile;
use App\Containers\AppSection\Media\Services\MediaService;
use App\Ship\Parents\Actions\Action as ParentAction;

final class DownloadMediaFileAction extends ParentAction
{
    public function __construct(private readonly MediaService $mediaService)
    {
    }

    public function run(
        string $url,
        int $folderId,
        int $userId,
        ?string $visibility = null,
        ?string $accessMode = null
    ): MediaFile
    {
        return $this->mediaService->downloadFromUrl($url, $folderId, $userId, $visibility, $accessMode);
    }
}
